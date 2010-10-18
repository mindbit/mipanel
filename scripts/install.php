<?
require_once "Env.php";
require_once "SrvCtl.php";

function mkpasswd($len = 16) {
	$ret = "";
	for ($i = 0; $i < $len; $i++) {
		$asc = mt_rand(0, 61);
		if ($asc < 10) {
			$ret .= chr($asc + 48);
			continue;
		}
		if ($asc < 36) {
			$ret .= chr($asc + 55);
			continue;
		}
		$ret .= chr($asc + 61);
	}
	return $ret;
}

function sqlUserPass($params) {
	echo "CREATE USER " . $params["DB_USER"] . ";\n";
	echo "ALTER USER " . $params["DB_USER"] . " PASSWORD '" .
		$params["DB_PASSWORD"] . "';\n";
}

function main() {
	Env::setup();

	$sc = new SrvCtl();
	$params = array(
			"DB_HOST" => "localhost",
			"DB_NAME" => "mipanel",
			);

	$params["DB_USER"] = "mipanel";
	$params["DB_PASSWORD"] = mkpasswd();
	$sc->setupMipanel($params);
	$sc->setupRedirect($params); //FIXME use different user
	$sc->setupMydns($params); //FIXME use different user
	sqlUserPass($params);

	$params["DB_USER"] = "vmail";
	$params["DB_PASSWORD"] = mkpasswd();
	$sc->setupDovecot($params);
	$sc->setupPostfix($params);
	sqlUserPass($params);

	$params["DB_USER"] = "proftpd";
	$params["DB_PASSWORD"] = mkpasswd();
	$sc->setupProftpd($params);
	sqlUserPass($params);

	$sc->setupHttpd();
}

main();
?>
