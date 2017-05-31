<?php
namespace Mindbit\Mipanel\View;

use Mindbit\Mpl\Search\SearchForm;
use Mindbit\Mipanel\Controller\DomainSearchRequest;
use Mindbit\Mpl\Util\HTML;

class DomainSearchForm extends SearchForm {
    function createRequest() 
    {
        return new DomainSearchRequest();
    }

    function getTitle() 
    {
        return "Domain: Search";
    }

    function displayForm() 
    {
        ?>
        <table>
        <tr>
            <td>Domain:</td>
            <td><input type="text" name="name" class="ui-widget" value="<?= HTML::entities($this->data["name"])?>"></td>
        </tr>
        <tr><td colspan="2" align="center"><input type="submit" class="ui-button" value="Search">
        </table>
        <?php
    }

    function displayResultsHeader() 
    {
        parent::displayResultsHeader();
        echo HTML::tableRow(HTML::TH, null, null,
            "Domain"
            );
    }

    function displayResult($result) 
    {
        echo HTML::tableRow(HTML::TD, null, null,
            $this->offset,
            array(HTML::link('domains.php', $result->getName()))
            );
    }
}