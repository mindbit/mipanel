<?
require_once "auth.php";
require_once "common.php";
require_once "RestDataSource.php";

class UsersRequest extends RestRequest {
	function createOm() {
		return new Users();
	}
}

$request = new UsersRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
