<?
require_once "common.php";
require_once "BaseForm.php";
require_once "controller/MipanelAuthRequest.php";
require_once "SmartClientAuthForm.php";

class MipanelAuthForm extends SmartClientAuthForm {
	function createRequest() {
		return new MipanelAuthRequest();
	}
}

$protector = new MipanelAuthForm();
$protector->write();

?>
