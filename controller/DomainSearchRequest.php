<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Search\BaseSearchRequest;
use Mindbit\Mipanel\Model\Mipanel\DomainQuery;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mipanel\Model\Mipanel\Map\DomainTableMap;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\SearchDecorator;

class DomainSearchRequest extends BaseSearchRequest
{
    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        $ret = new HtmlDecorator($ret, FormDecorator::BLOCK_CONTENT, 'application.domainsearch.html');
        $ret = new SearchDecorator($ret);
        return $ret;
    }

    protected function buildQuery()
    {
        $query = DomainQuery::create();
        $this->addLike($query, DomainTableMap::COL_DOMAIN, $_REQUEST['domain']);
        return $query->orderByDomain();
    }
}