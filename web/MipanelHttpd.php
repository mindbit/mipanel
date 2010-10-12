<?
require_once "SmartClientRPCResponse.php";
require_once "RequestDispatcher.php";
require_once "controller/MipanelHttpdRequest.php";

class MipanelHttpd extends RequestDispatcher {
	function createRequest() {
		return new MipanelHttpdRequest(new SmartClientRPCResponse());
	}

	function write() {
		echo $this->request->getResponse()->jsonEncode();
	}
}

$obj = new MipanelHttpd();
$obj->write();

?>
