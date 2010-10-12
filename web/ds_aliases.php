<?
require_once "auth.php";
require_once "common.php";
require_once "RestDataSource.php";

class AliasesRequest extends RestRequest {
	function createOm() {
		return new GlobalMailAliases();
	}

	function omToArray($om) {
		$ret=parent::omToArray($om);
		$domain=DomainsPeer::retrieveByPK($om->getDomainId());
		$ret["addressalias"]=$om->getName().'@'.$domain->getDomain();
		return $ret;
	}
}

$request = new AliasesRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
