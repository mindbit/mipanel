<?
require_once "Env.php";
Env::setup();

require_once "GetOpt.php";

define("SAFE_MIN_UID",		500);

set_time_limit(0);

/**
 * MiPanel class for controlling anything that needs root privileges.
 */
abstract class SrvCtl {
	protected $argv;
	protected $uid;

	static function getInstance($argv) {
		if (sizeof($argv) < 2)
			throw new Exception("Command not specified");
		$declaredClasses = get_declared_classes();
		$cls = null;
		foreach ($declaredClasses as $_cls) {
			if ($_cls == $argv[1] && is_subclass_of($_cls, "SrvCtl")) {
				$cls = $_cls;
				break;
			}
		}
		if ($cls === null)
			throw new Exception("Command not implemented: " . $argv[1]);
		$_argv = $argv;
		array_shift($_argv);
		array_shift($_argv);
		$ret = new $cls($_argv);
		$ret->setArgv($argv);
		return $ret;
	}

	function getOptions() {
		return array(
				'u' => array(GetOpt::OPT_ARG_REQUIRED, "uid")
				);
	}

	function setArgv($argv) {
		$this->argv = $argv;
	}

	function __construct($argv) {
		$opts = $this->getOptions();
		$pcfg = array();
		foreach ($opts as $opt => $cfg)
			$pcfg[$opt] = $cfg[0];
		$getOpt = new GetOpt($pcfg);
		$pcfg = $getOpt->parseArgs($argv);
		foreach ($pcfg as $opt => $cfg) {
			$property = $opts[$opt][1];
			$this->$property = $cfg[0];
		}
	}

	abstract function run();
}

class ServerSetup extends SrvCtl {
	function run() {
		var_dump($this);
	}
}

$srvCtl = SrvCtl::getInstance($_SERVER["argv"]);
$srvCtl->run();
?>
