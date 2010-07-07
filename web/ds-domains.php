<?
require_once "common.php";
require_once "RestDataSource.php";

class DomainsRequest extends RestRequest {
	function createOm() {
		return new Domains();
	}
}

$request = new DomainsRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
