<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "controller/MipanelRestRequest.php";

class AliasToRequest extends MipanelRestRequest {
	function createOm() {
		return new GlobalMailAliasTo();
	}
}

$request = new AliasToRequest();
$request->dispatch();
echo $request->getJsonResponse();
?>
