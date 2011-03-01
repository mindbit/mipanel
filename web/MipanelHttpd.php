<?
require_once "SmartClientRPCResponse.php";
require_once "RequestDispatcher.php";
require_once "controller/MipanelHttpdRequest.php";

class MipanelHttpd extends RequestDispatcher {
	function createRequest() {
		return new MipanelHttpdRequest(new SmartClientRPCResponse());
	}

	function write() {
		try {
			echo $this->request->getResponse()->jsonEncode();
		} catch (Exception $e) {
			ErrorHandler::logException($e);
			echo '{"status":' . SmartClientRPCResponse::STATUS_FAILURE . ',' .
				'"data":"Data Server Failure"}';
		}
	}
}

$obj = new MipanelHttpd();
$obj->write();

?>
