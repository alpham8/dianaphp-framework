<?php
/**
 * DBConnection.php
 *
 * this class is used for getting an opened connection to the database (singleton)
 * this is only a super class for inheritance for all common used DB Adapter (e. g. "MysqlAdapter")
 *
 * @package De.Twunner.Persistence.Sql
 * @version v0.0.1
 * @author Thomas Wunner <th.wunner@gmx.de>
 * @copyright CC by SA Copyrith (c) 2014, Thomas Wunner
 *
 *
 * @since API v0.0.1
 */
namespace Diana\Core\Persistence\Sql
{
    class DBConnection
    {
        protected static $_instance;
        protected $_defaultCrendens;
        protected $_bConnected = false;
        protected $_pdo;

        protected function __construct($sDsn, $sUser, $sPass)
        {

            $this->_defaultCrendens = array('dsn' => $sDsn,
                                            'user' => $sUser,
                                            'pass' => $sPass
                                            );

            $this->_pdo = new \PDO($sDsn, $sUser, $sPass);
            $this->_bConnected = true;
        }

        public static function getConnection($crendentials)
        {
            if (self::$_instance instanceof DBConnection)
            {
                return self::$_instance;
            }

            elseif (empty($crendentials))
            {
                $this->_defaultCrendens = array('dsn' => 'mysql:host=localhost;dbname=marktplatz',
                                                'user' => 'root',
                                                'pass' => 'geheim'
                                                );
                return self::$_instance = new DBConnection($this->_defaultCrendens['dsn'],
                                                              $this->_defaultCrendens['user'],
                                                              $this->_defaultCrendens['pass']
                                                             );
            }

            else
            {
                return self::$_instance = new DBConnection($crendentials['dsn'], $crendentials['user'], $crendentials['pass']);
            }
        }

        public function getPdo()
        {
            return $this->_pdo;
        }

        public function getNewPdo()
        {
            return $this->_pdo = new \PDO($this->_defaultCrendens['dsn'],
            							$this->_defaultCrendens['user'],
                                        $this->_defaultCrendens['pass']
                                        );
        }

        public function beginTransaction()
        {
            if (!$this->_pdo->inTransaction())
            {
                return $this->_pdo->beginTransaction();
            }
            return -1;
        }

        public function rollBack()
        {
            if ($this->_pdo->inTransaction())
            {
                return $this->_pdo->rollBack();
            }

            else
            {
                return false;
            }
        }

        public function commit()
        {
            if ($this->_pdo->inTransaction())
            {
                return $this->_pdo->commit();
            }

            else
            {
                return false;
            }
        }
    }
}
?>
