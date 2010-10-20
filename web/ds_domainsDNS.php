<?
require_once "common.php";
ErrorHandler::setHandler(new ThrowErrorHandler());

require_once "auth.php";
require_once "config.php";
require_once "RestDataSource.php";

class DomainsDNSRequest extends RestRequest {
	
	function createOm() {
		return new Domains();
	}

	function omToArray($om) {
		$ret=parent::omToArray($om);
		return $ret;
	}

	function doSave() {
		$domain = DomainsPeer::retrieveByPK($this->data["domain_id"]);
		$soa = SoaPeer::createDefaultConfig($domain->getDomain(), DEFAULT_DNS_IP);
		$domain->setSoaId($soa->getId());  
		$domain->save();		
	}
}

$request = new DomainsDNSRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
