<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Search\BaseSearchRequest;
use Mindbit\Mipanel\Model\Mipanel\MailMboxQuery;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mipanel\Model\Mipanel\Map\MailMboxTableMap;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\SearchDecorator;
use Mindbit\Mipanel\View\IconTheme;

class MailMboxesRequest extends BaseSearchRequest
{
    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret->addCssRef('css/main.css');
        if ($this->action == self::ACTION_FORM) {
            $ret = new HtmlDecorator($ret, HtmlResponse::BLOCK_BODY_INNER, 'application.searchwrapper.html');
            $ret = new FormDecorator($ret, 'application.search.wrapper.form');
            $ret->getTemplate()->setVariable(FormDecorator::VAR_TARGET, 'results');
        } else {
            $ret->getTemplate()->setVariables([
            'query.id'      => $_REQUEST['id'],
            'query.user'    => $_REQUEST['user'],
            ]);
            $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        }
        $ret = new HtmlDecorator($ret, FormDecorator::BLOCK_CONTENT, 'application.mailmboxes.html');
        $ret = new SearchDecorator($ret);
        return $ret;
    }

    protected function buildQuery()
    {
        $query = MailMboxQuery::create();
        $query->add(MailMboxTableMap::COL_MAIL_DOMAINS_ID, $_REQUEST['id']);
        $this->addLike($query, MailMboxTableMap::COL_USER, $_REQUEST['user']);
        return $query->orderByUser();
    }

    /**
     * {@inheritDoc}
     * @see \Mindbit\Mpl\Search\BaseSearchRequest::getRowVariables()
     *
     * @param \Mindbit\Mipanel\Model\Mipanel\MailMbox $om
     */
    protected function getRowVariables($om)
    {
        return [
            'id'        => $om->getId(),
            'user'      => $om->getUser(),
            ];
    }
}