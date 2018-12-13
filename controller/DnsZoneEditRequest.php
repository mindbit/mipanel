<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Mvc\Controller\SimpleFormRequest;
use Mindbit\Mipanel\Model\Mipanel\DnsZone;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\CrudDecorator;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mipanel\Model\Mipanel\Map\DnsZoneTableMap;
use Propel\Runtime\Map\TableMap;

class DnsZoneEditRequest extends SimpleFormRequest
{
    protected function createOm()
    {
        return new DnsZone();
    }

    protected function omFromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        parent::omFromArray($arr, $keyType);

        if ($this->action == self::ACTION_UPDATE) {
            $this->om->resetModified(DnsZoneTableMap::COL_ORIGIN);
        }
    }

    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        $ret = new CrudDecorator($ret);
        $ret = new HtmlDecorator($ret, FormDecorator::BLOCK_CONTENT, 'application.dnszoneedit.html');
        return $ret;
    }
}