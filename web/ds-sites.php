<?
require_once "common.php";
require_once "RestDataSource.php";

class SitesRequest extends RestRequest {
	function createOm() {
		return new Sites();
	}
	function doSave()
	{	
		if ($this->data["site_id"])
		{
			$site=SitesPeer::retrieveByPK($this->data["site_id"]);
			$site->setEnabled($this->data["enabled"]);	
			$site->save();
		}
		else
			parent::doSave();
	}
}

$request = new SitesRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
