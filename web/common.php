<?php

use Mindbit\Mpl\MPL;
use Mindbit\Mpl\Logging\SyslogLogger;

// Initialise autoloader
require __DIR__ . '/../vendor/autoload.php';

// Initialise MPL
MPL::init();
$log = new SyslogLogger("mipanel");
MPL::setLogger($log);

// Initialise Propel
require __DIR__ . '/../model/generated-conf/config.php';
