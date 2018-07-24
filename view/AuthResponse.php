<?php
namespace Mindbit\Mipanel\View;

use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mpl\Auth\BaseAuthRequest;
use Mindbit\Mpl\Template\Template;

class AuthResponse extends HtmlResponse
{
    const TEMPLATE_BODY = 'mindbit.mipanel.authresponse.html';

    public function send()
    {
        $this->addTitle('Mipanel Login');
        $this->addCssRef('css/style.css');
        $this->template->replaceBlock(self::BLOCK_BODY_INNER, Template::load(self::TEMPLATE_BODY));
        $this->template->setVariable('username', @$_REQUEST['username']);
        $this->template->setVariable('action', BaseAuthRequest::ACTION_LOGIN);

        if ($this->request->getStatus() == BaseAuthRequest::STATUS_FAILED) {
            $this->template->getBlock('auth.failed')->show();
        }

        $this->template->getBlock(self::BLOCK_BODY_INNER)->show();

        parent::send();
    }
}
