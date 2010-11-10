<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "controller/MipanelRestRequest.php";

class AliasesRequest extends MipanelRestRequest {
	function createOm() {
		return new GlobalMailAliases();
	}

	function omToArray($om) {
		$ret = parent::omToArray($om);
		$domain = DomainsPeer::retrieveByPK($om->getDomainId());
		$ret["addressalias"] = $om->getName() . '@' . $domain->getDomain();
		return $ret;
	}
}

$request = new AliasesRequest();
$request->dispatch();
echo $request->getJsonResponse();
?>
