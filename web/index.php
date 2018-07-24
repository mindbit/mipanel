<?php
use Mindbit\Mpl\MPL;
use Mindbit\Mpl\Logging\SyslogLogger;
use Mindbit\Mipanel\Controller\AuthRequest;
use Mindbit\Mpl\Template\Template;

// Initialise autoloader
require __DIR__ . '/../vendor/autoload.php';

// Initialise MPL
MPL::init();
$log = new SyslogLogger("mindbit");
MPL::setLogger($log);
Template::setLoadPath(array(
    __DIR__ . '/../vendor/mindbit/mpl/template',
    __DIR__ . '/../template'
));

// Initialise Propel
require __DIR__ . '/../model/generated-conf/config.php';

// Check/perform authentication
$authRequest = new AuthRequest();
$authRequest->handle();

$validPages = array();

if (isset($_REQUEST["page"]) && in_array($_REQUEST["page"],$validPages)) {
    $page = $_REQUEST["page"];
    $pageRequest = new $validPages[$page];
    $pageRequest->handle();
}
