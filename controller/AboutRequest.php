<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Mvc\Controller\BaseRequest;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;

class AboutRequest extends BaseRequest
{
    const DEFAULT_ACTION = 'view';

    /**
     * @brief Retrieve various information about the host OS and PHP
     *
     * See the following sections in the PHP manual:
     *  $_SERVER                    http://php.net/manual/en/reserved.variables.server.php
     *  PHP Options/Info Functions  http://php.net/manual/en/ref.info.php
     *  Predefined Constants        http://php.net/manual/en/reserved.constants.php
     */
    public function actionView()
    {
        $mipanelVersion = 'unknown';
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.git')) {
            $mipanelVersion = trim(shell_exec('git describe'));
        }

        $free = [];
        exec('free -m', $free);
        $free[1] = preg_split('/ +/', $free[1]);
        $free[2] = preg_split('/ +/', $free[2]);

        $load = explode(' ', file_get_contents('/proc/loadavg'));
        $load[3] = explode('/', $load[3]);

        $template = $this->response->getTemplate();
        $template->setVariables([
            'mipanel.version'       => $mipanelVersion,
            'server.software'       => $_SERVER['SERVER_SOFTWARE'],
            'php.sapi'              => PHP_SAPI,
            'php.version'           => PHP_VERSION,
            'os.type'               => php_uname('s'),
            'os.release'            => php_uname('r'),
            'os.version'            => php_uname('v'),
            'load.avg.1m'           => $load[0],
            'load.avg.5m'           => $load[1],
            'load.avg.10m'          => $load[2],
            'load.proc.running'     => $load[3][0],
            'load.proc.total'       => $load[3][1],
            'free.mem.total'        => $free[1][1],
            'free.mem.used'         => $free[1][2],
            'free.mem.free'         => $free[1][3],
            'free.mem.shared'       => $free[1][4],
            'free.mem.buf'          => $free[1][5],
            'free.mem.available'    => $free[1][6],
            'free.swap.total'       => $free[2][1],
            'free.swap.used'        => $free[2][2],
            'free.swap.free'        => $free[2][3],
        ]);
        $template->getBlock(HtmlDecorator::BLOCK_BODY_INNER)->show();
    }

    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        //$ret->addJsRef('js/tab.js');
        //$ret->addCssRef('css/main.css');
        $ret = new HtmlDecorator($ret, HtmlResponse::BLOCK_BODY_INNER, 'application.about.html');
        return $ret;
    }
}