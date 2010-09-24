<?
require_once "common.php";
require_once "GenericUser.php";

class MipanelUser implements GenericUser {
	protected $username;

	static function authenticate($username, $password) {
		$c = new Criteria();
		$c->add(UsersPeer::USERNAME, $username);
		$c->add(UsersPeer::PASSWORD, $password);

		$usr = UsersPeer::doSelect($c);
		if (empty($usr))
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
