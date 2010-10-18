<?
require_once "common.php";
require_once "SrvCtl.php";

if ($_SERVER["argc"] != 2 || ($_SERVER["argv"][1] != "start"
			&& $_SERVER["argv"][1] != "stop")) {
	echo "Usage: ".$_SERVER["argv"][0]." start|stop\n";
	die();
}

$srvCtl = new SrvCtl();

$c = new Criteria();
$c->add(DomainsPeer::SITE_ID, null, Criteria::ISNOTNULL);

$domains = DomainsPeer::doSelect($c);
foreach ($domains as $domain) {
	$site = $domain->getSites();
	$r = $srvCtl->sendHttpdSignal($site->getName(), $_SERVER["argv"][1], $domain->getUsername());
}

?>
