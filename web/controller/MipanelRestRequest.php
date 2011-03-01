<?
require_once "RestDataSource.php";

abstract class MipanelRestRequest extends RestRequest {
	function handleException($e) {
		ErrorHandler::logException($e);
	}
}

?>
