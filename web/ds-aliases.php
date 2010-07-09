<?
require_once "common.php";
require_once "RestDataSource.php";

class AliasesRequest extends RestRequest {
	function createOm() {
		return new GlobalMailAliases();
	}
}

$request = new AliasesRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
