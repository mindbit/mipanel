<?

/**
 * MiPanel class for controlling anything that needs root privileges.
 */
class SrvCtl {
	// for security reasons, this is defined as a constant
	const ROOT = "/var/www/mipanel";

	const SAFE_MIN_UID = 500;
	const SAFE_MIN_GID = 500;

	protected $httpdModulesPath;

	function __construct() {
		$this->httpdModulesPath = "/usr/lib/httpd/modules";
		if (file_exists("/usr/lib64/httpd/modules"))
			$this->httpdModulesPath = "/usr/lib64/httpd/modules";
	}

	protected static $validHttpdSignals =
		array("start", "restart", "graceful", "stop", "graceful-stop");

	function getRoot() {
		return self::ROOT;
	}

	protected function runQuiet($cmd, $cwd = null, $env = null) {
		$descriptorspec = array(
				0 => array("pipe", "r"),
				1 => array("file", "/dev/null", "a"),
				2 => array("file", "/dev/null", "a")
				);
		$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
		if ($process === false)
			return false;

		fclose($pipes[0]);
		return proc_close($process);
	}

	function userAdd($username, $homeDir) {
		if (!preg_match("/^[a-zA-Z0-9._-]+$/", $username))
			throw new Exception("Invalid username");

		if (!preg_match("/^[a-zA-Z0-9._-]+$/", $homeDir))
			throw new Exception("Invalid home directory");

		$cmd = "useradd -M -U -s /sbin/nologin -d " .
			escapeshellarg(self::ROOT . "/" . $homeDir) . " " .
			escapeshellarg($username);
		$res = $this->runQuiet($cmd);

		if ($res == 9)
			throw new Exception("Duplicate username");
		if ($res)
			throw new Exception("useradd failed with code " . $res);

		$uinfo = posix_getpwnam($username);
		assert($uinfo !== false);
		assert($uinfo["uid"] >= self::SAFE_MIN_UID);
		assert($uinfo["gid"] >= self::SAFE_MIN_GID);

		return array(
				"uid" => $uinfo["uid"],
				"gid" => $uinfo["gid"]
				);
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

		$siteRoot = self::ROOT . "/" . $siteName;
		
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
		return "su -s /bin/bash -c " . escapeshellarg($innerCmd) .
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
		return self::ROOT . "/" . $siteName . "/conf/httpd.conf";
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
}


?>
