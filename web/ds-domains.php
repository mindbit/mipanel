<?
require_once "common.php";
require_once "RestDataSource.php";

class DomainsRequest extends RestRequest {
	
	function createOm() {
		return new Domains();
	}
	function omToArray($om)
	{
		$ret=parent::omToArray($om);
		$domain=DomainsPeer::retrieveByPK($om->getDomainId());
		if ($om->getSiteId()!=null)
		{
			$site=SitesPeer::retrieveByPK($om->getSiteId());
			$ret["enabled"]=$site->getEnabled();
		}
		$ret["nr_mailboxes"]=$domain->countMailboxess();
		$ret["nr_aliases"]=$domain->countGlobalMailAliasess();
		
		return $ret;
	}
	function doSave()
	{	
		/*$con = Propel::getConnection(SitesPeer::DATABASE_NAME);
		
			$sql = "SELECT MAX(server_port) FROM sites";
			$stmt = $con->prepare($sql);
			//$stmt->execute(array(':domainId' => $this->om->getDomainId());
			$sites = SitesPeer::populateObjects($stmt);
			print_r($sites);
		*/
		if (isset($this->data["enable_web"]))
		{
			$site=new Sites();
			$name="www.".$this->data["domain"];
			$site->setName($name);	
			$site->setServerIp("127.0.0.1");
			$site->setServerPort("8000");
			$site->setEnabled("1");	
			$site->save();
			$site_alias=new SiteAliases();
			$site_alias->setName($this->data["domain"]);
			$site_alias->setSiteId($site->getSiteId());
			$site_alias->save();	
			$this->om->setSiteId($site->getSiteId());  

			/*$firstField = substr(SitesPeer::SERVER_PORT, strrpos(SitesPeer::SERVER_PORT, '.') + 1);
			$selc = new Criteria(SitesPeer::DATABASE_NAME);
			$selc->add(SitesPeer::SITE_ID,$site->getSiteId());
			//$updc=new Criteria(SitesPeer::DATABASE_NAME);
			$selc->add(SitesPeer::SERVER_PORT, array('raw' => $firstField), Criteria::CUSTOM_EQUAL);
			SitesPeer::doUpdate($selc,$con);*/

		}
		parent::doSave();
	}
	function doRemove() 
	{
		$domain=DomainsPeer::retrieveByPK($this->data["domain_id"]);
		$id=$domain->getSiteId();
		parent::doRemove();
		if ($id)
		{
			$sites=SitesPeer::retrieveByPK($domain->getSiteId());		
			SitesPeer::doDelete($sites);
		}
	}
}

$request = new DomainsRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
