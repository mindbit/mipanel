<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Auth\BaseAuthRequest;
use Mindbit\Mipanel\Model\Mipanel\UserQuery;
use Mindbit\Mipanel\View\AuthResponse;

class AuthRequest extends BaseAuthRequest
{
    protected function createResponse()
    {
        return new AuthResponse($this);
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