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
        protected static $instance;
        protected $defaultCrendens;
        protected $bConnected = false;
        protected $pdo;

        protected function __construct($sDsn, $sUser, $sPass)
        {
            $this->defaultCrendens = array('dsn' => $sDsn,
                                        'user' => $sUser,
                                        'pass' => $sPass
                                    );

            if (MYSQL_ENCODING !== '') {
                $this->pdo = new \PDO(
                                $sDsn,
                                $sUser,
                                $sPass,
                                array(
                                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . MYSQL_ENCODING
                                )
                            );
            } else {
                $this->pdo = new \PDO($sDsn, $sUser, $sPass);
            }
            $this->bConnected = true;
        }

        public static function getConnection($crendentials)
        {
            if (self::$instance instanceof DBConnection) {
                return self::$instance;
            } elseif (empty($crendentials)) {
                $this->defaultCrendens = array(
                                            'dsn' => 'mysql:host=localhost;dbname=marktplatz',
                                            'user' => 'root',
                                            'pass' => 'geheim'
                                        );
                return self::$instance = new DBConnection(
                                            $this->defaultCrendens['dsn'],
                                            $this->defaultCrendens['user'],
                                            $this->defaultCrendens['pass']
                                        );
            } else {
                return self::$instance = new DBConnection(
                                            $crendentials['dsn'],
                                            $crendentials['user'],
                                            $crendentials['pass']
                                        );
            }
        }

        public function getPdo()
        {
            return $this->pdo;
        }

        public function getNewPdo()
        {
            return $this->pdo = new \PDO(
                                    $this->defaultCrendens['dsn'],
                                    $this->defaultCrendens['user'],
                                    $this->defaultCrendens['pass']
                                );
        }

        public function beginTransaction()
        {
            if (!$this->pdo->inTransaction()) {
                return $this->pdo->beginTransaction();
            }

            return -1;
        }

        public function rollBack()
        {
            if ($this->pdo->inTransaction()) {
                return $this->pdo->rollBack();
            } else {
                return false;
            }
        }

        public function commit()
        {
            if ($this->pdo->inTransaction()) {
                return $this->pdo->commit();
            } else {
                return false;
            }
        }
    }
}
