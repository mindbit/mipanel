<?
require_once "SmartClientRPCResponse.php";
require_once "SmartClientAuthenticator.php";
require_once "controller/MipanelAuthRequest.php";

class MipanelAuthenticator extends SmartClientAuthenticator {
	function createRequest() {
		return new MipanelAuthRequest();
	}

	function getSessionData() {
		$user = MplSession::getUser();
		MplSession::setUser($user);
		return array(
				"username" => $user->getUsername(),
				);
	}
}

$obj = new MipanelAuthenticator();
$obj->write();
?>
