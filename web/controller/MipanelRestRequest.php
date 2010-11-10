<?
require_once "RestDataSource.php";

abstract class MipanelRestRequest extends RestRequest {
	function handleException($e) {
		$msgs = explode("\n", $e->__toString());
		foreach ($msgs as $msg)
			Env::log($msg, PEAR_LOG_ERR);
	}
}

?>
