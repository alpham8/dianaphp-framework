<?php
/**
 * Date.php
 *
 * This method implements a few common features, which does the DateTime class not have by default.
 * Like Java does: Returns the database correct formatted DateTime String.
 *
 * @package De.Twunner.Persistence.Sql
 * @version v0.0.1
 * @author Thomas Wunner <th.wunner@gmx.de>
 * @copyright CC by SA Copyrith (c) 2014, Thomas Wunner
 *
 *
 * @since API v0.0.1
 */

class Date extends DateTime
{
    public function __toString()
    {
        return new String($this->format('Y-m-d H:i:s'));
    }
}
?>