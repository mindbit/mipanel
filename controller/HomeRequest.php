<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Mvc\Controller\BaseRequest;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;

class HomeRequest extends BaseRequest
{
    const DEFAULT_ACTION = 'view';

    protected function actionView()
    {
        $template = $this->response->getTemplate();
        $template->getBlock(HtmlDecorator::BLOCK_BODY_INNER)->show();
    }

    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret->addJsRef('js/menu.js');
        $ret->addCssRef('css/main.css');
        $ret = new HtmlDecorator($ret, HtmlResponse::BLOCK_BODY_INNER, 'application.homeview.html');
        return $ret;
    }
}