<?
require_once("Env.php");
require_once "Log.php";
Env::setup();
Env::setLogger(Log::factory('syslog', LOG_LOCAL5, 'mipanel'));

require_once("Rmi.php");
require_once("SrvCtl.php");

$server = new StdInOutRmiServer();
$server->run();
?>
