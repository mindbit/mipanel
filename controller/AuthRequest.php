<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Auth\BaseAuthRequest;
use Mindbit\Mipanel\Model\Mipanel\UserQuery;
use Mindbit\Mipanel\View\AuthResponse;
use Mindbit\Mpl\Mvc\View\FormDecorator;
use Mindbit\Mpl\Mvc\View\HtmlResponse;

class AuthRequest extends BaseAuthRequest
{
    protected function createResponse()
    {
        $ret = new AuthResponse($this);
        $ret = new FormDecorator($ret, HtmlResponse::BLOCK_BODY_INNER);
        return $ret;
    }

    protected function authenticateUser()
    {
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $user = UserQuery::create()->findOneByUsername($username);
        if ($user == null) {
            return;
        }

        $salt = hex2bin($user->getSalt());
        $password = hash('sha256', $salt . $password);
        if ($password == $user->getPassword()) {
            return $user;
        }
    }
}