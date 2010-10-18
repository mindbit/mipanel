<?
// this should be run as root, using the following command:
// php -q -d include_path=/home/blade/mindbit/mpl TestSrvCtl.php

// -------- user / database input data ----------
$siteName = "www.test-srvctl.localdomain";
$username = "test-srvctl";
$port = 10000;
// ----------------------------------------------

$basePath = realpath(dirname(__FILE__));

require_once("Env.php");
Env::setup();

require_once("Rmi.php");
require_once("HttpdConf.php");

$cmd = "/usr/bin/php -q -d include_path=" .
escapeshellarg(get_include_path() . ":" . $basePath) . " " .
escapeshellarg($basePath . "/SrvCtlRmiServer.php");

$client = new ProcOpenRmiClient($cmd);

$srvCtl = $client->createInstance("SrvCtl");

// ------- begin server configuration code -------
$root = $srvCtl->getWebRoot();
$serverRoot = $root . "/" . $siteName;

$udata = $srvCtl->userAdd($username, $siteName);
$srvCtl->serverSetup($siteName, $udata["uid"], $udata["gid"]);

$httpdConf = new HttpdConf();
$httpdConf->setName($siteName);
$httpdConf->setPort($port);
$httpdConf->setServerRoot($serverRoot);
$srvCtl->updateServerConfig($siteName, $httpdConf->create(), $username);

$srvCtl->sendHttpdSignal($siteName, "start", $username);
?>
