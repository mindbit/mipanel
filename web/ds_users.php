<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "controller/MipanelRestRequest.php";

class UsersRequest extends MipanelRestRequest {
	function createOm() {
		return new Users();
	}
}

$request = new UsersRequest();
$request->dispatch();
echo $request->getJsonResponse();
?>
