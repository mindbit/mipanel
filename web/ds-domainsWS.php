<?
require_once "common.php";
require_once "RestDataSource.php";

class DomainsWSRequest extends RestRequest {
	
	function createOm() {
		return new Domains();
	}
	function omToArray($om)
	{
		$ret=parent::omToArray($om);
		return $ret;
	}
	function doSave()
	{	
		$domain=DomainsPeer::retrieveByPK($this->data["domain_id"]);
		$site=new Sites();
		$name="www.".$domain->getDomain();
		$site->setName($name);	
		$site->setServerIp("127.0.0.1");
		$site->setServerPort("8000");
		$site->setEnabled("0");	
		$site->save();

		$site_alias=new SiteAliases();
		$site_alias->setName($this->data["domain"]);
		$site_alias->setSiteId($site->getSiteId());
		$site_alias->save();	
		
		$domain->setSiteId($site->getSiteId());  
		$domain->save();		
	
		//parent::doSave();
	}
}

$request = new DomainsWSRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
