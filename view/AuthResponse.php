<?php
namespace Mindbit\Mipanel\View;

use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mpl\Auth\BaseAuthRequest;
use Mindbit\Mpl\Template\Template;
use Mindbit\Mpl\Mvc\View\FormDecorator;

class AuthResponse extends HtmlResponse
{
    const TEMPLATE_BODY = 'application.authresponse.html';

    public function send()
    {
        $this->addTitle('Mipanel Login');
        $this->addCssRef('css/style.css');
        $this->template->replaceBlock(FormDecorator::BLOCK_CONTENT, Template::load(self::TEMPLATE_BODY));
        $this->template->setVariable('username', @$_REQUEST['username']);
        $this->template->setVariable('auth', BaseAuthRequest::ACTION_LOGIN);

        if ($this->request->getStatus() == BaseAuthRequest::STATUS_FAILED) {
            $this->template->getBlock('auth.failed')->show();
        }

        $this->template->getBlock(FormDecorator::BLOCK_SUBMIT)->hide();
        $this->template->getBlock(self::BLOCK_BODY_INNER)->show();

        parent::send();
    }
}
