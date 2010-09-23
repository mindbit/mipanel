<?
require_once "BaseAuthRequest.php";
require_once "model/MipanelUser.php";

class MipanelAuthRequest extends BaseAuthRequest {
	function authenticateUser($username, $password) {
		return MipanelUser::authenticate($username, $password);
	}
}

?>
