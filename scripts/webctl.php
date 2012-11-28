<?
require_once "common.php";
require_once "SrvCtl.php";

$allowedActions = array(
		"start",
		"stop",
		"graceful"
		);

if ($_SERVER["argc"] != 2 || !in_array($_SERVER["argv"][1], $allowedActions)) {
	echo "Usage: ".$_SERVER["argv"][0]." start|stop\n";
	exit(1);
}

$srvCtl = new SrvCtl();

$c = new Criteria();
$c->add(DomainsPeer::SITE_ID, null, Criteria::ISNOTNULL);

$domains = DomainsPeer::doSelect($c);
foreach ($domains as $domain) {
	$site = $domain->getSites();
	switch ($_SERVER["argv"][1]) {
	case 'reload':
		if ($srcCtl->httpdAlive())
			$srvCtl->sendHttpdSignal($site->getName(), "graceful", $domain->getUsername);
		break;
	default:
		$srvCtl->sendHttpdSignal($site->getName(), $_SERVER["argv"][1], $domain->getUsername());
	}
}

?>
