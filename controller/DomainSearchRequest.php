<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Search\BaseSearchRequest;
use Mindbit\Mipanel\Model\Mipanel\DomainQuery;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mipanel\Model\Mipanel\Map\DomainTableMap;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\SearchDecorator;
use Mindbit\Mipanel\View\IconTheme;

class DomainSearchRequest extends BaseSearchRequest
{
    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        if ($this->action == self::ACTION_FORM) {
            $ret = new HtmlDecorator($ret, HtmlResponse::BLOCK_BODY_INNER, 'application.searchwrapper.html');
            $ret = new FormDecorator($ret, 'application.search.wrapper.form');
            $ret->getTemplate()->setVariable(FormDecorator::VAR_TARGET, 'results');
        } else {
            $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        }
        $ret = new HtmlDecorator($ret, FormDecorator::BLOCK_CONTENT, 'application.domainsearch.html');
        $ret = new SearchDecorator($ret);
        return $ret;
    }

    protected function buildQuery()
    {
        $query = DomainQuery::create();
        $this->addLike($query, DomainTableMap::COL_NAME, $_REQUEST['name']);
        return $query->orderByName();
    }

    /**
     * {@inheritDoc}
     * @see \Mindbit\Mpl\Search\BaseSearchRequest::getRowVariables()
     *
     * @param \Mindbit\Mipanel\Model\Mipanel\Domain $om
     */
    protected function getRowVariables($om)
    {
        return [
            'id'        => $om->getId(),
            'name'      => $om->getName(),
            'img.dns'   => IconTheme::boolIcon($om->getDnsZonesId()),
            'img.mail'  => IconTheme::boolIcon($om->getMailDomainsId())
        ];
    }
}
