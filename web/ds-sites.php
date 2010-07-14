<?
require_once "common.php";
require_once "RestDataSource.php";

class SitesRequest extends RestRequest {
	function createOm() {
		return new Sites();
	}
}

$request = new SitesRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
