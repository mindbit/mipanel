<?
require_once "GenericUser.php";

class MipanelUser implements GenericUser {
	protected $username;

	static function authenticate($username, $password) {
		if ($username != "admin" || $password != "1234")
				return null;
		$ret = new MipanelUser();
		$ret->username = $username;
		return $ret;
	}

	function getUsername() {
		return $this->username;
	}
}

?>
