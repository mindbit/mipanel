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
		$ret["nr_mailboxes"]=$domain->countMailboxess();
		$ret["nr_aliases"]=$domain->countGlobalMailAliasess();
		return $ret;
	}
	function doSave()
	{	
		//if ($_POST["enable_web"])
		//{
					
			$site=new Sites();
			$name="www.".$this->om->getDomain();
			$site->setName($name);	
			$site->setServerIp("127.0.0.1");
			$site->setServerPort("80");
			$site->setEnabled("1");	
			$site_alias=new SiteAliases();
			$site_alias->setName($this->om->getDomain());
			$site_alias->save();	
			$this->om->setSiteId($site_alias->getSiteId()); //? 
			
		//}
		parent::doSave();
	}
}

$request = new DomainsRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
