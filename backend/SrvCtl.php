<?

/**
 * MiPanel class for controlling anything that needs root privileges.
 */
class SrvCtl {
	// for security reasons, these are defined as constants
	const SAFE_MIN_UID			= 500;
	const SAFE_MIN_GID			= 500;

	const WEB_ROOT				= "/var/www/mipanel";
	const POSTFIX_ROOT			= "/etc/postfix";
	const TEMPLATE_ROOT			= "/usr/lib/mipanel/templates";
	const DOVECOT_CONF			= "/etc/dovecot.conf";
	const DOVECOT_SQL_CONF		= "/etc/dovecot-sql.conf";
	const PROFTPD_PAM_CONF		= "/etc/pam.d/proftpd";
	const PROFTPD_PAM_SQL		= "/etc/pam-pgsql-proftpd.conf";
	const REDIRECT_CONF			= "/etc/mipanel/redirect.conf";
	const MYDNS_CONF			= "/etc/mydns.conf";
	const HTTPD_ROOT			= "/etc/httpd";
	const MIPANEL_ROOT			= "/usr/lib/mipanel";
	const MAIL_ROOT				= "/var/mail/virtual";

	const CHKCONFIG				= "/sbin/chkconfig";
	const USERADD				= "/usr/sbin/useradd";
	const USERDEL				= "/usr/sbin/userdel";
	const SU					= "/bin/su";

	protected $httpdModulesPath;

	function __construct() {
		$this->httpdModulesPath = "/usr/lib/httpd/modules";
		if (file_exists("/usr/lib64/httpd/modules"))
			$this->httpdModulesPath = "/usr/lib64/httpd/modules";
	}

	protected static $validHttpdSignals =
		array("start", "restart", "graceful", "stop", "graceful-stop");

	function getWebRoot() {
		return self::WEB_ROOT;
	}

	protected function runQuiet($cmd, $cwd = null, $env = null) {
		$descriptorspec = array(
				0 => array("pipe", "r"),
				1 => array("file", "/dev/null", "a"),
				2 => array("file", "/dev/null", "a")
				);
		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
		if ($process === false)
			throw new Exception("Failed executing: " . $cmd);

		fclose($pipes[0]);
		return proc_close($process);
	}

	static function validUsername($username) {
		return preg_match("/^[a-zA-Z0-9._-]+$/", $username);
	}

	static function validName($name) {
		return preg_match("/^[a-zA-Z0-9._-]+$/", $name);
	}

	function userAdd($username, $homeDir) {
		if (!self::validUsername($username))
			throw new Exception("Invalid username");

		if (!self::validName($homeDir))
			throw new Exception("Invalid home directory");

		$cmd = self::USERADD . " -M -s /sbin/nologin -d " .
			escapeshellarg(self::WEB_ROOT . "/" . $homeDir) . " " .
			escapeshellarg($username);
		$res = $this->runQuiet($cmd);

		if ($res == 9)
			throw new DuplicateUserException("Duplicate username");
		if ($res)
			throw new Exception("useradd failed with code " . $res);

		$uinfo = posix_getpwnam($username);
		assert($uinfo !== false);
		assert($uinfo["uid"] >= self::SAFE_MIN_UID);
		assert($uinfo["gid"] >= self::SAFE_MIN_GID);

		/*
		 * Manually create the home directory, even if web is not
		 * enabled. This is required for ftp.
		 */
		$siteRoot = self::WEB_ROOT . "/" . $homeDir;
		mkdir($siteRoot, 0755, true);
		chmod($siteRoot, 0750);
		$this->chown($siteRoot, $uinfo["uid"], $uinfo["gid"]);

		return array(
				"uid" => $uinfo["uid"],
				"gid" => $uinfo["gid"]
				);
	}


	/**
	 * Create only root directory for all domain mail; individual
	 * directories for mailboxes are automatically created by postfix
	 * on first mail delivery.
	 */
	function createMaildirRoot($uid, $gid, $domain) {
		if (!self::validName($domain))
			throw new Exception("Invalid domain");
		if ($uid < self::SAFE_MIN_UID || $gid < self::SAFE_MIN_GID)
			throw new Exception("Bad uid/gid");

		$mailRoot = self::MAIL_ROOT . "/" . $domain;
		mkdir($mailRoot, 0750);
		$this->chown($mailRoot, $uid, $gid);
	}

	protected function mkdir($path, $mode, $user, $group) {
		if (!mkdir($path, $mode))
			throw new Exception("Cannot create directory " . $path);
		$this->chown($path, $user, $group);
	}

	protected function chown($path, $user, $group) {
		if (!chown($path, $user))
			throw new Exception("Cannot change ownership of " . $path);
		if (!chgrp($path, $group))
			throw new Exception("Cannot change group of " . $path);
	}

	function serverSetup($siteName, $uid, $gid) {
		$uid = (int)$uid;
		$gid = (int)$gid;
		if (!preg_match("/^[a-zA-Z0-9._-]+$/", $siteName))
			throw new Exception("Invalid site name");
		if ($uid < self::SAFE_MIN_UID || $gid < self::SAFE_MIN_GID)
			throw new Exception("Bad uid/gid");

		$siteRoot = self::WEB_ROOT . "/" . $siteName;
		
		if (!is_dir($siteRoot))
			$this->mkdir($siteRoot, 0750, $uid, $gid);
		$this->mkdir($siteRoot . "/conf", 0755, $uid, $gid);
		$this->mkdir($siteRoot . "/logs", 0755, $uid, $gid);
		$this->mkdir($siteRoot . "/run", 0755, $uid, $gid);
		$this->mkdir($siteRoot . "/tmp", 0755, $uid, $gid);
		$this->mkdir($siteRoot . "/web", 0755, $uid, $gid);

		if (!symlink($this->httpdModulesPath, $siteRoot . "/modules"))
			throw new Exception("Cannot create modules symlink in " . $siteRoot);
	}

	function getHttpdCmd($configFile, $args, $username) {
		$innerCmd = "/usr/sbin/httpd -f " . escapeshellarg($configFile) .
			" " . $args;
		return self::SU . " -s /bin/bash -c " . escapeshellarg($innerCmd) .
			" " . escapeshellarg($username);
	}

	protected function __checkServerConfig($configFile, $username) {
		$cmd = $this->getHttpdCmd($configFile, "-t", $username);

		$descriptorspec = array(
				0 => array("pipe", "r"),
				1 => array("file", "/dev/null", "a"),
				2 => array("pipe", "w")
				);
		$process = proc_open($cmd, $descriptorspec, $pipes);
		if ($process === false)
			throw new Exception("Error running httpd");

		fclose($pipes[0]);
		$output = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$status = proc_close($process);
		return $status ? $output : false;
	}

	function getServerConfigPath($siteName) {
		return self::WEB_ROOT . "/" . $siteName . "/conf/httpd.conf";
	}

	function checkServerConfig($siteName, $username) {
		return $this->__checkServerConfig($this->getServerConfigPath($siteName), $username);
	}

	function updateServerConfig($siteName, $config, $username) {
		$tmp = tempnam(sys_get_temp_dir(), "mipanel.");
		$fp = fopen($tmp, "w");
		$status = fwrite($fp, $config);
		assert($status == strlen($config)); // FIXME
		fclose($fp);

		$uinfo = posix_getpwnam($username);
		assert($uinfo !== false);
		$this->chown($tmp, $uinfo["uid"], $uinfo["gid"]);

		if (($err = $this->__checkServerConfig($tmp, $username)) !== false)
			throw new Exception("Failed to validate config: " . $err);

		$cfg = $this->getServerConfigPath($siteName);
		if (!rename($tmp, $cfg))
			throw new Exception("Could write new config file: " . $cfg);

		if (!chmod($cfg, 0644))
			throw new Exception("Cannot change mode of " . $cfg);
	}

	function sendHttpdSignal($siteName, $signal, $username) {
		if (!in_array($signal, self::$validHttpdSignals))
			throw new Exception("Invalid httpd signal: " . $signal);
		$cfg = $this->getServerConfigPath($siteName);
		$cmd = $this->getHttpdCmd($cfg, "-k " . escapeshellarg($signal), $username);
		return $this->runQuiet($cmd);
	}

	function httpdAlive($siteName) {
		$pidFile = self::WEB_ROOT . "/" . $siteName . "/run/httpd.pid";

		if (!file_exists($pidFile))
			return false;

		$pid = (int)file_get_contents($pidFile);

		return posix_kill($pid, 0);
	}

	protected function templateReplace($dest, $src, $params) {
		$pairs = array();
		foreach ($params as $k => $v)
			$pairs['$' . '{' . $k . '}'] = $v;
		$buf = file_get_contents($src);
		file_put_contents($dest, strtr($buf, $pairs));
	}

	protected function backupFile($path) {
		if (!file_exists($path))
			return true;
		$backupPath = $path . ".mipanel.bak";
		if (file_exists($backupPath))
			return false;
		rename($path, $backupPath);
	}

	function setupDovecot($params) {
		// dovecot config file
		$this->backupFile(self::DOVECOT_CONF);
		$this->templateReplace(self::DOVECOT_CONF, self::TEMPLATE_ROOT . "/dovecot/dovecot.conf", array());
		chmod(self::DOVECOT_CONF, 0644);

		// database access file
		$this->templateReplace(self::DOVECOT_SQL_CONF, self::TEMPLATE_ROOT . "/dovecot/dovecot-sql.conf", $params);
		chmod(self::DOVECOT_SQL_CONF, 0600);

		$this->runQuiet(self::CHKCONFIG . " dovecot on");
	}

	function setupPostfix($params) {
		$path = self::POSTFIX_ROOT . "/main.cf";
		$this->backupFile($path);
		$this->templateReplace($path, self::TEMPLATE_ROOT . "/postfix/main.cf", array());
		chmod($path, 0644);

		$path = self::POSTFIX_ROOT . "/master.cf";
		$this->backupFile($path);
		$this->templateReplace($path, self::TEMPLATE_ROOT . "/postfix/master.cf", array());
		chmod($path, 0644);

		$files = array(
				"pgsql-gid-maps.cf",
				"pgsql-mbox-domains.cf",
				"pgsql-mbox-maps.cf",
				"pgsql-uid-maps.cf",
				"pgsql-virtual-maps.cf"
				);
		foreach ($files as $file) {
			$path = self::POSTFIX_ROOT . "/" . $file;
			$this->templateReplace($path, self::TEMPLATE_ROOT . "/postfix/" . $file, $params);
			chgrp($path, "postfix");
			chmod($path, 0640);
		}

		$this->runQuiet(self::CHKCONFIG . " postfix on");
	}

	function setupProftpd($params) {
		$this->backupFile(self::PROFTPD_PAM_CONF);
		$this->templateReplace(self::PROFTPD_PAM_CONF, self::TEMPLATE_ROOT . "/pam.d/proftpd", array());
		chmod(self::PROFTPD_PAM_CONF, 0644);

		$this->templateReplace(self::PROFTPD_PAM_SQL, self::TEMPLATE_ROOT . "/pam-pgsql/pam-pgsql-proftpd.conf", $params);
		chmod(self::PROFTPD_PAM_SQL, 0600);

		$this->runQuiet(self::CHKCONFIG . " proftpd on");
	}

	function setupRedirect($params) {
		$this->backupFile(self::REDIRECT_CONF);
		$this->templateReplace(self::REDIRECT_CONF, self::TEMPLATE_ROOT . "/redirect/redirect.conf", $params);
		chgrp(self::REDIRECT_CONF, "squid");
		chmod(self::REDIRECT_CONF, 0640);
	}

	function setupMydns($params) {
		$this->backupFile(self::MYDNS_CONF);
		$this->templateReplace(self::MYDNS_CONF, self::TEMPLATE_ROOT . "/mydns/mydns.conf", $params);
		chmod(self::MYDNS_CONF, 0600);
		$this->runQuiet(self::CHKCONFIG . " mydns on");
	}

	function setupHttpd() {
		$path = self::HTTPD_ROOT . "/conf.d/ssl.conf";
		$this->backupFile($path);
		$this->templateReplace($path, self::TEMPLATE_ROOT . "/httpd/ssl.conf", array());
		$this->runQuiet(self::CHKCONFIG . " httpd on");
	}

	function setupMipanel($params) {
		$path = self::MIPANEL_ROOT . "/backend/model/build/conf/mipanel-conf.php";
		$this->templateReplace($path, self::TEMPLATE_ROOT . "/mipanel/mipanel-conf.php", $params);
		chgrp($path, "apache");
		chmod($path, 0640);

		// enable other services that mipanel depends on and are not
		// configured separately
		$this->runQuiet(self::CHKCONFIG . " postgresql on");
	}

	function serverCleanup($userName, $siteName, $removeUser=true) {
		/*
		 * FIXME: we need to add a delay, because if the start process
		 * hasn't finished the stop command will not work. Also it's
		 * possible that httpdAlive() returns false if we call this
		 * method too soon.
		 */
		sleep(1);
		if ($this->httpdAlive($siteName))
			$this->sendHttpdSignal($siteName, "stop", $userName);
		if ($removeUser === true) {
			$this->runQuiet(self::USERDEL . " -r ".escapeshellarg($userName));
			$this->runQuiet("rm -rf ".escapeshellarg(self::MAIL_ROOT . "/" . substr($siteName, 4)));
		}
		else
			$this->runQuiet("rm -rf ".escapeshellarg(self::WEB_ROOT . "/" . $siteName) . "/*");
	}
}

class DuplicateUserException extends Exception {
};


?>
