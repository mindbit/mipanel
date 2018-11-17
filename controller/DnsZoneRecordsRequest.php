<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Search\BaseSearchRequest;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\SearchDecorator;
use Mindbit\Mipanel\Model\Mipanel\DnsRecord;
use Mindbit\Mipanel\Model\Mipanel\DnsRecordQuery;
use Mindbit\Mipanel\Model\Mipanel\Map\DnsRecordTableMap;

class DnsZoneRecordsRequest extends BaseSearchRequest
{
    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        if ($this->action == self::ACTION_FORM) {
            $ret = new HtmlDecorator($ret, HtmlResponse::BLOCK_BODY_INNER, 'application.searchwrapper.html');
            $ret = new FormDecorator($ret, 'application.search.wrapper.form');
            $ret->getTemplate()->setVariable(FormDecorator::VAR_TARGET, 'results');
        } else {
            $ret->getTemplate()->setVariables([
                'query.id'      => $_REQUEST['id'],
                'query.name'    => $_REQUEST['name'],
            ]);
            $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        }
        $ret = new HtmlDecorator($ret, FormDecorator::BLOCK_CONTENT, 'application.dnszonerecords.html');
        $ret = new SearchDecorator($ret);
        return $ret;
    }

    protected function buildQuery()
    {
        $query = DnsRecordQuery::create();
        $query->add(DnsRecordTableMap::COL_ZONE, $_REQUEST['id']);
        $this->addLike($query, DnsRecordTableMap::COL_NAME, $_REQUEST['name']);
        return $query->orderByType()->orderByName();
    }

    /**
     * {@inheritDoc}
     * @see \Mindbit\Mpl\Search\BaseSearchRequest::getRowVariables()
     *
     * @param \Mindbit\Mipanel\Model\Mipanel\DnsRecord $om
     */
    protected function getRowVariables($om)
    {
        return [
            'id'        => $om->getId(),
            'name'      => $om->getName(),
            'type'      => $om->getType(),
            'aux'       => $om->getType() == DnsRecord::TYPE_MX ? $om->getAux() : '&nbsp;',
            'data'      => $om->getData(),
            'ttl'       => $om->getTtl(),
        ];
    }
}
