<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

/* @inline */ require_once "auth.php";
require_once "controller/MipanelRestRequest.php";

class SiteAliasesRequest extends MipanelRestRequest {
	function createOm() {
		return new SiteAliases();
	}

	/*function doSave() {
		if ($this->data["site_id"]) 
		{
			$site=SitesPeer::retrieveByPK($this->data["site_id"]);
			$site->setEnabled($this->data["enabled"]);	
			$site->save();
		}
		else
			parent::doSave();
	}*/

	function checkAuthToken() {
		/* @checkAuthToken */
	}

	function dispatch() {
		$this->checkAuthToken();
		parent::dispatch();
	}
}

$request = new SiteAliasesRequest();
$request->dispatch();
echo $request->getJsonResponse();
?>
