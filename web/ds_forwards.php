<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "RestDataSource.php";

class FowardsRequest extends RestRequest {
	function createOm() {
		return new MailboxForwards();
	}
}

$request = new FowardsRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
