<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Mvc\Controller\BaseRequest;
use Mindbit\Mipanel\Model\Mipanel\DomainQuery;
use Mindbit\Mpl\Mvc\View\HtmlResponse;
use Mindbit\Mpl\Mvc\View\HtmlDecorator;

class DomainViewRequest extends BaseRequest
{
    const DEFAULT_ACTION = 'view';

    public function actionView()
    {
        $om = (new DomainQuery())->findPK($_REQUEST['id']);
        $tabs = [];
        if ($om->getDnsZonesId()) {
            $tabs += [
                'DNS Zone'      => 'dns-zone-edit?id=' . $om->getDnsZonesId(),
                'DNS Records'   => 'dns-zone-records?id=' . $om->getDnsZonesId(),
            ];
        }
        if ($om->getMailDomainsId()) {
            $tabs += [
                'Mailboxes'     => 'mail-mboxes?id=' . $om->getMailDomainsId(),
                'Mail Aliases'  => 'mail-aliases?id=' . $om->getMailDomainsId(),
            ];
        }
        $template = $this->response->getTemplate();
        $this->addTabs($tabs, $template->getBlock('application.tab'));
        $template->setVariables([
            'name'          => $om->getName(),
            'content.url'   => array_shift($tabs),
        ]);
        $template->getBlock(HtmlDecorator::BLOCK_BODY_INNER)->show();
    }

    /**
     * @param array $tabs
     * @param \Mindbit\Mpl\Template\Block $block
     */
    protected function addTabs($tabs, $block)
    {
        $count = 0;
        $class = 'tab-active';
        foreach ($tabs as $text => $url)
        {
            $id = strtolower(str_replace(" ","_",$text));
            $block->setVariables([
                'tab.class' => $class,
                'tab.text'  => $text,
                'tab.url'   => $url,
                'tab.id'    => $id,
                'tab.count' => $count++
            ]);
            $block->show();
            $class = 'tab-inactive';
        }
    }

    protected function createResponse()
    {
        $ret = new HtmlResponse($this);
        $ret->addJsRef('js/tab.js');
        $ret->addCssRef('css/main.css');
        $ret = new HtmlDecorator($ret, HtmlResponse::BLOCK_BODY_INNER, 'application.domainview.html');
        return $ret;
    }
}