<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "controller/MipanelRestRequest.php";

class MailboxesRequest extends MipanelRestRequest {
	function createOm() {
		return new Mailboxes();

	}

	function omToArray($om) {
		$ret=parent::omToArray($om);
		$domain=DomainsPeer::retrieveByPK($om->getDomainId());
		$ret["addressmail"]=$om->getMailbox().'@'.$domain->getDomain();
		$ret["nr_forwards"]=$om->countMailboxForwardss();
		return $ret;
	}
}

$request = new MailboxesRequest();
$request->dispatch();
echo $request->getJsonResponse();
?>
