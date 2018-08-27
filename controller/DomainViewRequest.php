<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Mvc\Controller\BaseRequest;
use Mindbit\Mipanel\Model\Mipanel\DomainQuery;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;

class DomainViewRequest extends BaseRequest
{
    const DEFAULT_ACTION = 'view';

    public function actionView()
    {
        $om = (new DomainQuery())->findPK($_REQUEST['id']);
        $this->response->getTemplate()->setVariables([
            'domain'        => $om->getDomain(),
            'dns_zones_id'  => $om->getDnsZonesId(),
        ]);
        $this->response->getTemplate()->getBlock(HtmlDecorator::BLOCK_BODY_INNER)->show();
    }

    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret = new HtmlDecorator($ret, HtmlResponse::BLOCK_BODY_INNER, 'application.domainview.html');
        return $ret;
    }
}