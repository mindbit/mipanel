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
		$ret["enabled"]="-1";
		if ($om->getSiteId()!=null)
		{
			$site=SitesPeer::retrieveByPK($om->getSiteId());
			if ($site->getEnabled()=="0")
				$ret["enabled"]="-1";
			else $ret["enabled"]=$site->getEnabled();
		}
		$ret["nr_mailboxes"]=$domain->countMailboxess();
		$ret["nr_aliases"]=$domain->countGlobalMailAliasess();
		
		return $ret;
	}
	function doSave()
	{	
		if (isset($this->data["enable_web"]))
		{
			$c = new Criteria();
			$c->addDescendingOrderByColumn(SitesPeer::SERVER_PORT);
			$c->setLimit(1);	
			$mysites=SitesPeer::doSelect($c);
			$max_server_port='8000';
			if ($mysites)	
				foreach($mysites as $mysite)
				{
					$max_server_port=$mysite->getServerPort()+1;
				}
			$site=new Sites();
			$name="www.".$this->data["domain"];
			$site->setName($name);	
			$site->setServerIp("127.0.0.1");
			$site->setServerPort($max_server_port);
			$site->setEnabled("0");	
			$site->save();
			$site_alias=new SiteAliases();
			$site_alias->setName($this->data["domain"]);
			$site_alias->setSiteId($site->getSiteId());
			$site_alias->save();	
			$this->om->setSiteId($site->getSiteId());  

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
