<?php
use Mindbit\Mipanel\View\AuthForm;
use Mindbit\Mpl\Session\Session;

require_once 'common.php';

$authForm = new AuthForm();
$authForm->write();

$user = Session::getUser();
print "Welcome " . $user->getUsername() . "!";