<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "controller/MipanelRestRequest.php";

class FowardsRequest extends MipanelRestRequest {
	function createOm() {
		return new MailboxForwards();
	}
}

$request = new FowardsRequest();
$request->dispatch();
echo $request->getJsonResponse();
?>
