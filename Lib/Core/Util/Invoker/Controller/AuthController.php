<?php
/**
 * The base method for each PageController that needs an Authentification
 *
 * @package De.Twunner.Util.Invoker.Controller
 *
 * @version v0.0.1
 * @author Thomas Wunner <th.wunner@gmx.de>
 * @copyright CC by SA Copyrith (c) 2014, Thomas Wunner
 *
 *
 * @since API v0.0.1
 */

class AuthController extends AbstractPage
{
    public function preExec()
    {
        // TODO: is allowed? => $allowed
        // Implementierung der ACL mit isAllowed() und Rechte Liste
        $allowed  = true;
        if ($allowed)
        {

        }
        else
        {
            $this->view = 'notallowed';
        }
    }
}

?>
