<?php
namespace Mindbit\Mipanel\View;

use Mindbit\Mipanel\View\DomainSearchForm;

require_once 'auth.php';

$form = new DomainSearchForm();
$form->write();