<?php
use Mindbit\Mpl\MPL;
use Mindbit\Mpl\Logging\SyslogLogger;
use Mindbit\Mipanel\Controller\AuthRequest;
use Mindbit\Mpl\Template\Template;

// Initialise autoloader
require __DIR__ . '/../vendor/autoload.php';

// Initialise MPL
MPL::init();
MPL::setLogger(new SyslogLogger("mindbit"));
Template::setLoadPath(array(
    __DIR__ . '/../vendor/mindbit/mpl/template',
    __DIR__ . '/../template'
));

// Initialise Propel
require __DIR__ . '/../model/generated-conf/config.php';

// Check/perform authentication
$authRequest = new AuthRequest();
$authRequest->handle();

$routes = [
    'domain-search'     => 'DomainSearchRequest',
    'domain-view'       => 'DomainViewRequest',
    'dns-zone-edit'     => 'DnsZoneEditRequest',
    'dns-zone-records'  => 'DnsZoneRecordsRequest',
    'dns-record-edit'   => 'DnsRecordEditRequest',
    'mail-mboxes'       => 'MailMboxesRequest',
    'mail-mbox-edit'    => 'MailMboxEditRequest',
    'mail-aliases'      => 'MailAliasesRequest',
    'mail-alias-edit'   => 'MailAliasEditRequest',
];
$class = '\\Mindbit\\Mipanel\\Controller\\' . (@$routes[$_REQUEST['page']] ?: 'HomeRequest');
/**
 * @var \Mindbit\Mpl\Mvc\Controller\BaseRequest $controller
 */
$controller = new $class;
$controller->handle();
$controller->getResponse()->send();
