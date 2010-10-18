<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "RestDataSource.php";
require_once "Rmi.php";
require_once "HttpdConf.php";

class DomainsWSRequest extends RestRequest {
	
	function createOm() {
		return new Domains();
	}

	function omToArray($om) {
		$ret = parent::omToArray($om);
		return $ret;
	}

	function doSave() {
		$pdo = Propel::getConnection(SitesPeer::DATABASE_NAME);
		$client = new ProcOpenRmiClient("sudo ".RMI_SERVER_PATH." 2>&1");
		$srvCtl = $client->createInstance("SrvCtl");

		$siteName = "www.".$this->data["domain"];
		$u = explode(".", $this->data["domain"]);
		$user = posix_getpwnam($u[0]);

		try {
			$pdo->beginTransaction();

			$c = new Criteria();
			$c->addDescendingOrderByColumn(SitesPeer::SERVER_PORT);
			$c->setLimit(1);
			$mysites = SitesPeer::doSelect($c);
			$max_server_port = 10000;
			if ($mysites)
				foreach($mysites as $mysite) {
					$max_server_port = $mysite->getServerPort()+1;
				}

			$site = new Sites();
			$site->setName($siteName);
			$site->setServerIp("127.0.0.1");
			$site->setServerPort($max_server_port);
			$site->setEnabled("0");
			$site->save();

			$site_alias = new SiteAliases();
			$site_alias->setName($this->data["domain"]);
			$site_alias->setSiteId($site->getSiteId());
			$site_alias->save();

			$domain = DomainsPeer::retrieveByPK($this->data["domain_id"]);
			$domain->setSiteId($site->getSiteId());
			$domain->save();

			/* setup the site root folder */
			$serverRoot = $srvCtl->getWebRoot() . "/".$site->getName();
			$srvCtl->serverSetup($site->getName(), $user["uid"], $user["gid"]);

			$httpdConf = new HttpdConf();
			$httpdConf->setName($site->getName());
			$httpdConf->setPort($max_server_port);
			$httpdConf->setServerRoot($serverRoot);

			$srvCtl->updateServerConfig($site->getName(), $httpdConf->create(), $user["name"]);
			$srvCtl->sendHttpdSignal($site->getName(), "start", $user["name"]);

			$pdo->commit();
		} catch (Exception $e) {
			$pdo->rollback();
			$srvCtl->serverCleanup($user["name"], $siteName, false);
			throw $e;
		}
	}
}

$request = new DomainsWSRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
