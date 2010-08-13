<?
require_once("Env.php");
Env::setup();

require_once("Rmi.php");
require_once("SrvCtl.php");

$server = new StdInOutRmiServer();
$server->run();
?>
