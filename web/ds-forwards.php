<?
require_once "common.php";
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
