<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Mvc\Controller\SimpleFormRequest;
use Mindbit\Mpl\Mvc\View\CrudDecorator;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mipanel\Model\Mipanel\MailAlias;

class MailAliasEditRequest extends SimpleFormRequest
{
    protected function createOm()
    {
        return new MailAlias();
    }

    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        $ret = new CrudDecorator($ret);
        $ret = new HtmlDecorator($ret, FormDecorator::BLOCK_CONTENT, 'application.mailaliasedit.html');
        return $ret;
    }
}