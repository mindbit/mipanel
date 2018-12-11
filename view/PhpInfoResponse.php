<?php
namespace Mindbit\Mipanel\View;

use Mindbit\Mpl\Mvc\View\BaseResponse;

class PhpInfoResponse extends BaseResponse
{
    public function send()
    {
        phpinfo();
    }
}