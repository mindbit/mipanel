<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mipanel\Model\Mipanel\DnsRecord;
use Mindbit\Mipanel\View\DnsRecordDecorator;
use Mindbit\Mpl\Mvc\Controller\SimpleFormRequest;
use Mindbit\Mpl\Mvc\View\CrudDecorator;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;

class DnsRecordEditRequest extends SimpleFormRequest
{
    protected function createOm()
    {
        return new DnsRecord();
    }

    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        $ret = new CrudDecorator($ret);
        $ret = new DnsRecordDecorator($ret);
        return $ret;
    }
}