<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "controller/MipanelRestRequest.php";

class SitesRequest extends MipanelRestRequest {
	function createOm() {
		return new Sites();
	}

	function doSave() {
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
echo $request->getJsonResponse();
?>
