<?
require_once "common.php";
require_once "RestDataSource.php";

class MailboxesRequest extends RestRequest {
	function createOm() {
		return new Mailboxes();
	}
}

$request = new MailboxesRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
