<?php
namespace Mindbit\Mipanel\View;

use Mindbit\Mipanel\Model\Mipanel\DnsRecord;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\Options;

class DnsRecordDecorator extends HtmlDecorator
{
    const TEMPLATE_FORM = 'application.dnsrecordedit.html';

    const BLOCK_FORM_OPTIONS_TYPE = 'application.form.options.type';

    protected static $typeOptions = [
        DnsRecord::TYPE_A       => 'A',
        DnsRecord::TYPE_AAAA    => 'AAAA',
        DnsRecord::TYPE_CNAME   => 'CNAME',
        DnsRecord::TYPE_MX      => 'MX',
        DnsRecord::TYPE_NS      => 'NS',
        DnsRecord::TYPE_TXT     => 'TXT',
    ];

    public function __construct($component)
    {
        parent::__construct($component, FormDecorator::BLOCK_CONTENT, self::TEMPLATE_FORM);
    }

    public function send()
    {
        $this->addSelect(self::BLOCK_FORM_OPTIONS_TYPE, Options::buildFromSimpleMap(
            self::$typeOptions,
            $this->request->getOm()->getType()
        ));
        parent::send();
    }
}