<?php
namespace Mindbit\Mipanel\View;

use Mindbit\Mpl\Mvc\View\BaseForm;
use Mindbit\Mipanel\Controller\AuthRequest;
use Mindbit\Mpl\Auth\BaseAuthRequest;
use Mindbit\Mpl\Util\HTTP;

class AuthForm extends BaseForm {
    function createRequest()
    {
        return new AuthRequest();
    }

    function form()
    {
        ?>
        <table width="100%" height="100%" border="0">
          <tr>
             <td align="center">
               <table border="1" cellpadding="0" cellspacing="0">
               <tr>
                    <td>
                        <font style="font-family: sans-serif; font-size: 14pt;">Mipanel Authentication</font>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <br/><br/>
                    <table border="0">
                    <tr>
                        <td width="80">Username:</td>
                        <td><input type="text" name="username" value="<?= HTTP::inVar("username", "")?>" size="15"></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="password" value="" size="15"></td>
                    </tr>
                        </table>
                  <br/><?php
                  if ($this->request->getState() == BaseAuthRequest::S_AUTH_FAILED)
                      echo "<span style='color:red;'>Authentication failed!</span><br>";else "<br/><br/>";
                  ?><br/>
                    <input type="submit" style="background-color: #575BB7; color: #FFFFFF" name="aut_submit_button" value="Autentificare" onmouseover="this.style.color='#9194E5'" onmouseout="this.style.color='#FFFFFF'">
                    <input type="hidden" name="operationType" value="<?= BaseAuthRequest::OP_LOGIN?>">
                    <br/><br/>
                    </td>
                </tr>
                </table>
            </td>
            </tr>
            </table>
            <?php
    }

    function write()
    {
        switch ($this->request->getState()) {
            case BaseAuthRequest::S_AUTH_SUCCESS:
            case BaseAuthRequest::S_AUTH_CACHED:
            return;
        }
        parent::write();
        exit;
    }

    function css()
    {
        parent::css();
        $this->cssTag("css/style.css");
    }

    function getTitle()
    {
        return "Mipanel Login";
    }

    function getFormAttributes()
    {
        return array(
            "method"    => "post",
            "action"    => $_SERVER['PHP_SELF']
        );
    }
}