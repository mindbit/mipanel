<?php
namespace Mindbit\Mipanel\Controller;

use Mindbit\Mpl\Search\BaseSearchRequest;
use Mindbit\Mipanel\Model\Mipanel\DomainQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class DomainSearchRequest extends BaseSearchRequest 
{
    function initData()
    {
        return array(
            "name"      => ""
        );
    }

    function initPager()
    {
        $c = DomainQuery::create()
            ->filterByName('%'.$this->data['name'].'%', Criteria::LIKE)
            ->orderByName();
       // $c->setIgnoreCase(true);
        $this->setQueryPager($c);
    }
}