<?php

/**
 * Skeleton subclass for performing query and update operations on the 'soa' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.mipanel
 */
class SoaPeer extends BaseSoaPeer {
	static function createDefaultConfig($domainName, $defaultIp) {
		// make sure $domainName ends in a '.'
		if (substr($domainName, -1) != '.')
			$domainName .= '.';

		$soa = new Soa();
		$soa->setOrigin($domainName);
		$soa->setNs("ns.".$domainName);
		$soa->setMbox("hostmaster.".$domainName);
		$soa->setSerial(date("Ymd")."01");
		$soa->setActive('Y');
		$soa->save();

		$rr = new Rr();
		$rr->setName("");
		$rr->setType("NS");
		$rr->setData("ns.".$domainName);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();

		$rr->setName("");
		$rr->setType("MX");
		$rr->setData("mail.".$domainName);
		$rr->setAux("10");
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();

		$rr->setName("");
		$rr->setType("A");
		$rr->setData($defaultIp);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();

		$rr->setName("mail");
		$rr->setType("A");
		$rr->setData($defaultIp);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();

		$rr->setName("ns");
		$rr->setType("A");
		$rr->setData($defaultIp);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();

		$rr->setName("www");
		$rr->setType("CNAME");
		$rr->setData($domainName);
		$rr->setZone($soa->getId());
		$rr->save();
		$rr->clear();

		$rr->setName("");
		$rr->setType("TXT");
		$rr->setZone($soa->getId());
		$rr->setData("v=spf1 a mx ~all");
		$rr->save();

		return $soa;
	}

} // SoaPeer
