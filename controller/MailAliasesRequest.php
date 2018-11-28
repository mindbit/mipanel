<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Search\BaseSearchRequest;
use Mindbit\Mipanel\Model\Mipanel\MailAliasQuery;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mipanel\Model\Mipanel\Map\MailAliasTableMap;
use Mindbit\Mipanel\Model\Mipanel\Map\MailMboxTableMap;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\SearchDecorator;
use Mindbit\Mipanel\View\IconTheme;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;

class MailAliasesRequest extends BaseSearchRequest
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
                'query.user'    => $_REQUEST['user'],
                'query.alias'   => $_REQUEST['alias'],
            ]);
            $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        }
        $ret = new HtmlDecorator($ret, FormDecorator::BLOCK_CONTENT, 'application.mailaliases.html');
        $ret = new SearchDecorator($ret);
        return $ret;
    }

    protected function buildQuery()
    {
        $join = new Join();
        $join->addCondition(MailAliasTableMap::COL_MAIL_DOMAINS_ID, MailMboxTableMap::COL_MAIL_DOMAINS_ID);
        $join->addCondition(MailAliasTableMap::COL_USER, MailMboxTableMap::COL_USER);
        $join->setJoinType(Criteria::LEFT_JOIN);

        $query = MailAliasQuery::create();
        $query->add(MailAliasTableMap::COL_MAIL_DOMAINS_ID, $_REQUEST['id']);
        $query->addJoinObject($join);
        $query->addUsingAlias(MailMboxTableMap::COL_ID, null, Criteria::ISNULL);
        $this->addLike($query, MailAliasTableMap::COL_USER, $_REQUEST['user']);
        $this->addLike($query, MailAliasTableMap::COL_ALIAS, $_REQUEST['alias']);
        return $query->orderByUser();
    }

    /**
     * {@inheritDoc}
     * @see \Mindbit\Mpl\Search\BaseSearchRequest::getRowVariables()
     *
     * @param \Mindbit\Mipanel\Model\Mipanel\MailAlias $om
     */
    protected function getRowVariables($om)
    {
        return [
            'id'        => $om->getId(),
            'user'      => $om->getUser(),
            'alias'     => $om->getAlias(),
        ];
    }
}