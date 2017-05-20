<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Auth\BaseAuthRequest;
use Mindbit\Mipanel\Model\Mipanel\UserQuery;

class AuthRequest extends BaseAuthRequest
{

    function authenticateUser($username, $password)
    {
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