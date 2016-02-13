<?php
/**
 * MysqlAdapter.php
 *
 * this class is used for getting an opened connection to the mysql database (singleton)
 *
 * @package De.Twunner.Persistence.Sql
 * @version v0.0.1
 * @author Thomas Wunner <th.wunner@gmx.de>
 * @copyright CC by SA Copyrith (c) 2014, Thomas Wunner
 *
 *
 * @since API v0.0.1
 */

class MysqlAdapter extends DBConnection
{
    protected function __construct($strDsn, $strUser, $strPass)
    {
        $this->_adapter = 'mysql';
        parent($strDsn, $strUser, $strPass);
    }

    public function execSql ($strSql)
    {
        if ($this->_blConnected)
        {
        }
        // TODO: Entweder als direktes SQL, oder als doSelect, doUpdate, usw.
    }
}
?>
