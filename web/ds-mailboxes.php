<?
require_once "common.php";
require_once "RestDataSource.php";

class MailboxesRequest extends RestRequest {
	function createOm() {
		return new Mailboxes();

	}
	function omToArray($om)
	{
		$ret=parent::omToArray($om);
		$domain=DomainsPeer::retrieveByPK($om->getDomainId());
		$ret["addressmail"]=$om->getMailbox().'@'.$domain->getDomain();
		return $ret;
	}
}

$request = new MailboxesRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
