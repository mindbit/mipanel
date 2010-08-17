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

		$c = new Criteria();
		$c->addDescendingOrderByColumn(SitesPeer::SERVER_PORT);
		$c->setLimit(1);	
		$mysites=SitesPeer::doSelect($c);
		if ($mysites)	
			foreach($mysites as $mysite)
			{
				$max_server_port=$mysite->getServerPort()+1;
			}
		else $max_server_port='8000';

		$domain=DomainsPeer::retrieveByPK($this->data["domain_id"]);
		$site=new Sites();
		$name="www.".$domain->getDomain();
		$site->setName($name);	
		$site->setServerIp("127.0.0.1");
		$site->setServerPort($max_server_port);
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
