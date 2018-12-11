<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mipanel\View\PhpInfoResponse;
use Mindbit\Mpl\Mvc\Controller\BaseRequest;

class PhpInfoRequest extends BaseRequest
{
    const DEFAULT_ACTION = 'view';

    protected function actionView()
    {
    }

    protected function createResponse()
    {
        return new PhpInfoResponse($this);
    }
}