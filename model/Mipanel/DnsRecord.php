<?php

namespace Mindbit\Mipanel\Model\Mipanel;

use Mindbit\Mipanel\Model\Mipanel\Base\DnsRecord as BaseDnsRecord;

/**
 * Skeleton subclass for representing a row from the 'dns_records' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class DnsRecord extends BaseDnsRecord
{
    const TYPE_A        = 'A';
    const TYPE_AAAA     = 'AAAA';
    const TYPE_ALIAS    = 'ALIAS';
    const TYPE_CNAME    = 'CNAME';
    const TYPE_HINFO    = 'HINFO';
    const TYPE_MX       = 'MX';
    const TYPE_NAPTR    = 'NAPTR';
    const TYPE_NS       = 'NS';
    const TYPE_PTR      = 'PTR';
    const TYPE_RP       = 'RP';
    const TYPE_SRV      = 'SRV';
    const TYPE_TXT      = 'TXT';
}
