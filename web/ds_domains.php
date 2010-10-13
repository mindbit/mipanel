<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "config/config.php";
require_once "RestDataSource.php";
require_once "Rmi.php";
require_once "HttpdConf.php";

class DomainsRequest extends RestRequest {
	
	function createOm() {
		return new Domains();
	}

	function omToArray($om) {
		$ret=parent::omToArray($om);
		$domain=DomainsPeer::retrieveByPK($om->getDomainId());
		$ret["enabled"]="-1";
		if ($om->getSiteId()!=null) {
			$site=SitesPeer::retrieveByPK($om->getSiteId());
			if ($site->getEnabled()=="0")
				$ret["enabled"]="-1";
			else $ret["enabled"]=$site->getEnabled();
		}
		$ret["nr_mailboxes"]=$domain->countMailboxess();
		$ret["nr_aliases"]=$domain->countGlobalMailAliasess();
		
		return $ret;
	}

	function doSave() {
		if (!isset($this->data["username"])) {
			$username = explode(".",$this->data["domain"]);
			$this->data["username"] = $username[0];
		}

		/* create the system user */
		$client = new ProcOpenRmiClient("sudo ".RMI_SERVER_PATH." 2>&1");

		$srvCtl = $client->createInstance("SrvCtl");
		$siteName = "www.".$this->data["domain"];

		$pdo = Propel::getConnection(DomainsPeer::DATABASE_NAME);

		try {
			$pdo->beginTransaction();

			$user = $srvCtl->userAdd($this->data["username"], $siteName);

			if (isset($this->data["enable_web"]) && $this->data["enable_web"] == true) {
				$c = new Criteria();
				$c->addDescendingOrderByColumn(SitesPeer::SERVER_PORT);
				$c->setLimit(1);
				$mysites=SitesPeer::doSelect($c);
				$max_server_port = 8000;
				if ($mysites)
					foreach ($mysites as $mysite) {
						$max_server_port = $mysite->getServerPort() + 1;
					}
				$site = new Sites();
				$site->setName($siteName);
				$site->setServerIp(IP_DEFAULT);
				$site->setServerPort($max_server_port);
				$site->setEnabled("0");
				$site->save();
				$site_alias = new SiteAliases();
				$site_alias->setName($this->data["domain"]);
				$site_alias->setSiteId($site->getSiteId());
				$site_alias->save();
				$this->om->setSiteId($site->getSiteId());

				/* setup httpd root folder */
				$serverRoot = $srvCtl->getWebRoot() . "/".$siteName;
				$srvCtl->serverSetup($siteName, $user["uid"], $user["gid"]);

				$httpdConf = new HttpdConf();
				$httpdConf->setName($siteName);
				$httpdConf->setPort($max_server_port);
				$httpdConf->setServerRoot($serverRoot);

				$srvCtl->updateServerConfig($siteName, $httpdConf->create(), $this->data["username"]);
				$srvCtl->sendHttpdSignal($siteName, "start", $this->data["username"]);
			}

			if (isset($this->data["enable_dns"]) && $this->data["enable_dns"]==true) {
				$soa=new Soa();
				$name=$this->data["domain"].".";
				$soa->setOrigin($name);
				$soa->setNs("ns.".$name);
				$soa->setMbox("hostmaster.".$name);
				$soa->setSerial(date("Ymd")."01");
				$soa->setActive('Y');
				$soa->save();

				$rr=new Rr();
				$rr->setName("");
				$rr->setType("NS");
				$rr->setData("ns.".$name);
				$rr->setZone($soa->getId());
				$rr->save();
				$rr->clear();

				$rr->setName("");
				$rr->setType("MX");
				$rr->setData("mail.".$name);
				$rr->setAux("10");
				$rr->setZone($soa->getId());
				$rr->save();
				$rr->clear();

				$rr->setName("");
				$rr->setType("A");
				$rr->setData(IP_DEFAULT);
				$rr->setZone($soa->getId());
				$rr->save();
				$rr->clear();

				$rr->setName("mail");
				$rr->setType("A");
				$rr->setData(IP_DEFAULT);
				$rr->setZone($soa->getId());
				$rr->save();
				$rr->clear();

				$rr->setName("www");
				$rr->setType("CNAME");
				$rr->setData($name);
				$rr->setZone($soa->getId());
				$rr->save();
				$rr->clear();

				$rr->setName("");
				$rr->setType("TXT");
				$rr->setZone($soa->getId());
				$rr->setData("v=spf1 a mx ~all");
				$rr->save();

				$this->om->setSoaId($soa->getId());
			}

			parent::doSave();

			$pdo->commit();
		} catch (Exception $e) {
			$pdo->rollback();
			$srvCtl->serverCleanup($this->data["username"], $siteName);
			throw $e;
		}
	}

	function doRemove() {
		$domain=DomainsPeer::retrieveByPK($this->data["domain_id"]);
		$id=$domain->getSiteId();
		parent::doRemove();
		if ($id) {
			$sites=SitesPeer::retrieveByPK($domain->getSiteId());		
			SitesPeer::doDelete($sites);

			$soa=SoaPeer::retrieveByPK($domain->getSoaId());		
			SoaPeer::doDelete($soa);
		}
	}
}

$request = new DomainsRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
