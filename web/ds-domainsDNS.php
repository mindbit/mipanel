<?
require_once "auth.php";
require_once "config/config.php";
require_once "common.php";
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
		$domain=DomainsPeer::retrieveByPK($this->data["domain_id"]);
		$soa=new Soa();
		$name=$domain->getDomain().".";
		$soa->setOrigin($name);	
		$soa->setNs("ns.".$name);
		$soa->setMbox("hostmaster.".$name);
		$soa->setSerial(date("Ymd")."01");
		$soa->setActive('Y');
		$soa->save();

		$rr=new Rr();
		$rr->setName("");
		$rr->setType("NS");
		$rr->setData("ns.".$name);
		$rr->setZone($soa->getId());
		$rr->save();
		//$rr->setNew(true);
		$rr->clear();	

		$rr->setName("");
		$rr->setType("MX");
		$rr->setData("mail.".$name);
		$rr->setAux("10");
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();	

		$rr->setName("");
		$rr->setType("A");
		$rr->setData(IP_DEFAULT);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();	

		$rr->setName("mail");
		$rr->setType("A");
		$rr->setData(IP_DEFAULT);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();	

		$rr->setName("www");
		$rr->setType("CNAME");
		$rr->setData($name);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();	

		$rr->setName("");
		$rr->setType("TXT");
		$rr->setZone($soa->getId());
		$rr->setData("v=spf1 a mx ~all");
		$rr->save();
		
		$domain->setSoaId($soa->getId());  
		$domain->save();		
	}
}

$request = new DomainsDNSRequest();
$request->dispatch();
echo $request->getResponse()->jsonEncode();
?>
