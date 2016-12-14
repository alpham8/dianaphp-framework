<?php
namespace Diana\Core\Persistence\Sql
{
    use Diana\Core\Std\String;
    use Diana\Core\Std\Date;
    use Diana\Core\Persistence\Sql\DBConnection;
    use Diana\Core\Persistence\Sql\ModelException;

    class BaseModel
    {
        const CRITERIA_EQUALS = 'eq';
        const CRITERIA_LESSER_THAN = 'lt';
        const CRITERIA_GREATER_THAN = 'gt';
        const CRITERIA_GREATER_THAN_EQUALS = 'gteq';
        const CRITERIA_LESSER_THAN_EQUALS = 'lteq';
        const CRITERIA_NOT_EQUALS = '!=';
        const CRITERIA_IS_NOT_NULL = 'IS NOT NULL';
        const CRITERIA_IS_NULL = 'IS NULL';
        const CRITERIA_IN = 'in';
        const CRITERIA_LIKE = 'LIKE';
        const CRITERIA_CONDITION_CONNECTOR = 'connector';

        const CRITERIA_VALUE = 'value';
        const CRITERIA_OPERATOR = 'key';
        const CRITERIA_OR = 'OR';
        const CRITERIA_AND = 'AND';

        const ORDER_ASC = 'ASC';
        const ORDER_DESC = 'DESC';

        protected $db;
        protected $arJoin = array();
        protected $sTableName;
        protected $arColumns = array();
        protected $arColumnData = array();
        protected $arKeys = array();

        protected $sSQL;
        protected $sSelect;
        protected $sJoin;
        protected $sWhere;
        protected $sOrderBy;

        protected $stmt;
        protected $arWhere = array();
        protected $arObjects = array();
        protected $bThrowsEx = true;
        protected $arBindValues;
        protected $bCursorFetching = false;

        public function __construct()
        {
            $this->db = DBConnection::getConnection(array(
                            'dsn' => DB_DSN,
                            'user' => DB_USER,
                            'pass' => DB_PASS
                            ));
            $pdo = $this->db->getPdo();

            // Column Datatype conversion in PHP.
            // If this is not set, anything would be a string value.
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);

            // throw Exception on SQL error.
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        public function beginTransaction()
        {
            return $this->db->beginTransaction();
        }

        public function rollBack()
        {
            return $this->db->rollBack();
        }

        public function commit()
        {
            return $this->db->commit();
        }

        public function getKeys()
        {
            return $this->arKeys;
        }

        public function getColumns()
        {
            return $this->arColumns;
        }

        public function getSelect()
        {
            return $this->sSelect;
        }

        public function getJoin()
        {
            return $this->sJoin;
        }

        public function getWhere()
        {
            return $this->sWhere;
        }

        public function getTableName()
        {
            return $this->sTableName;
        }

        public function &getObjects()
        {
            return $this->arObjects;
        }

        public function setErrorHandling($bIsException)
        {
            $this->bThrowsEx = $bIsException;
        }

        /**
         * encodes a model name string to a table name string
         * e. g. ProfileClicks => profile_clicks
         * @param String $sTableName the model name (decoded table)
         * @return String the encoded table name
         */
        protected function encodeTableName($sTableName)
        {
            $sTable = $sTableName instanceof String ? $sTableName : new String($sTableName);
            $arMatches = array();

            if ($sTable->matches('/[A-Z]/', $arMatches)) {
                foreach ($arMatches[0] as $sMatch) {
                    $sMatch = new String($sMatch);
                    $sTable = $sTable->replace($sMatch, '_' . $sMatch->toLower());
                }
            }

            return $sTable->startsWith('_') ? $sTable->substring(1) : $sTable;
        }

        /**
         * decodes a table name string to a Model string
         * e. g. profile_clicks => ProfileClicks
         * @param String $sTableName the table name encoded
         * @return string the decoded table name
         */
        protected function decodeTableName($sTableName)
        {
            $sTable = $sTableName instanceof String ? $sTableName : new String($sTableName);
            $arMatches = array();

            if ($sTable->matches('/\_[a-z]/', $arMatches)) {
                foreach ($arMatches[0] as $sMatch) {
                    $sMatch = new String($sMatch);
                    $sMatchLetter = $sMatch->substring(1);
                    $sTable = $sTable->replace($sMatch, $sMatchLetter->toUpper());
                }
            }

            return $sTable->ucFirst();
        }

        /**
         * finds a foreign key pair in own model and others
         * @param String $sTableName the table name that should be looked for foreign key
         * @param boolean $isOwnTable (passed by reference) indicates if the foreign key is in own table or not
         * @return array of the found foreign key(s)
         */
        protected function findTableFk($sTableName, &$bIsOwnTable)
        {
            $sFound = '';
            $bIsOwnTable = true;
            $sTableName = $this->decodeTableName($sTableName)->__toString();
            $sTableNameEsc = $this->encodeTableName($sTableName)->__toString();

            foreach ($this->arKeys as $sKey => $sTable) {
                if (is_array($sTable)) {
                    if (in_array($sTableNameEsc, $sTable)) {
                        $sFound = $sKey;
                        break;
                    }
                } elseif (
                          is_string($sTable)
                          && $sTableNameEsc === $sTable
                ) {
                    $sFound = $sKey;
                    break;
                }
            }

            if ($sFound === '') {
                $bIsOwnTable = false;
                $sModel = "App\\Mvc\\Model\\" . $sTableName . 'Model';
                $model = new $sModel();
                $arKeys = $model->getKeys();
                $sTable = $this->encodeTableName($this->sTableName)->__toString();

                foreach ($arKeys as $sKey => $sTables) {
                    if (is_array($sTables) && in_array($sTable, $sTables)) {
                        $sFound = $sKey;
                        break;
                    } elseif ($sTables === $sTable) {
                        $sFound = $sKey;
                        break;
                    }
                }
            }

            return $sFound;
        }

        protected function findTableFKComplex($sTableName)
        {
            $sTableName = $sTableName instanceof String ? $sTableName : new String($sTableName);
            $sTable = $this->encodeTableName($sTableName)->__toString();
            $sFound = '';
            // TODO: In allen Join Models nach einer Verbindung suchen => es ist ein join auf einen join

            foreach ($this->arJoin as $sJoin) {
                $sTableModel = "App\\Mvc\Model\\" . $this->decodeTableName($sJoin)->__toString() . 'Model';
                $mdl = new $sTableModel();
                $arKeys = $mdl->getKeys();

                if (!$sJoin->equals($sTableName)) {
                    if (count($arKeys) > 0) {
                        foreach ($arKeys as $sKey => $sTables) {
                            if (is_array($sTables) && in_array($sTable, $sTables)) {
                                $sFoundTable = $sTables[$sTable];
                                $foundMdl = $mdl;
                                $sFound = $sKey;
                                break;
                            } elseif ($sTables === $sTable) {
                                $sFoundTable = $sTables;
                                $foundMdl = $mdl;
                                $sFound = $sKey;
                                break;
                            }
                        }
                    }
                } else {
                    if (count($arKeys) > 0) {
                        foreach ($arKeys as $sKey => $sTables) {
                            if (is_array($sTables)) {
                                foreach ($sTables as $sJoinTable) {
                                    if (in_array($sJoinTable, $this->arJoin)) {
                                        $sFoundTable = $sTables[$sTable];
                                        $foundMdl = $mdl;
                                        $sFound = $sKey;
                                        break;
                                    }
                                }


                                if ($sFound !== '') {
                                    break;
                                }
                            } elseif (in_array($sTables, $this->arJoin)) {
                                $sFoundTable = $sTables;
                                $foundMdl = $mdl;
                                $sFound = $sKey;
                                break;
                            }
                        }
                    }
                }

                if ($sFound !== '') {
                    break;
                }
            }

            return array(
                         $this->encodeTableName($foundMdl->getTableName())
                         . '.'
                         . $sFound =>
                            $this->encodeTableName($sFoundTable)
                            . '.id'
                        );
        }

        public function buildSelect(array $arCriteria = null)
        {
            $this->sSelect = new String('SELECT');

            $arOwnColumns = $this->arColumns;

            if (
                array_filled($arCriteria)
                && array_key_exists('distinctField', $arCriteria)
            ) {
                $sDistinctField = $arCriteria['distinctField'] instanceof String
                                    ? $arCriteria['distinctField']
                                    : new String($arCriteria['distinctField']);
                $arDistinctField = $sDistinctField->replace(SQL_ESC, '')->splitToStringsBy('.');

                $arTestColumns = array_keys($arOwnColumns);

                if (
                    $arDistinctField[0]->equals($this->sTableName)
                    && in_array($arDistinctField[1]->__toString(), $arTestColumns)
                ) {
                    $this->sSelect = new String($this->sSelect
                                        . ' distinct '
                                        . $arCriteria['distinctField']
                                        . ' AS '
                                        . SQL_ESC
                                        . $this->sTableName
                                        . '.'
                                        . $arDistinctField[1]
                                        . SQL_ESC
                                        . ',');
                    unset($arOwnColumns[$arDistinctField[1]->__toString()]);
                } elseif (in_array($arDistinctField[0], $this->arJoin)) {
                    $this->sSelect = new String($this->sSelect
                                        . ' distinct '
                                        . $arCriteria['distinctField']
                                        . ' AS '
                                        . SQL_ESC
                                        . $arDistinctField[0]
                                        . '.'
                                        . $arDistinctField[1]
                                        . SQL_ESC
                                        . ',');
                }
            }

            // TODO: aggregate function also for joining tables, not only for the own table...
            if (
                array_filled($arCriteria)
                && array_key_exists('aggregate', $arCriteria)
            ) {
                $sTableColumn = new String(array_keys($arCriteria['aggregate'])[0]);
                $sTableColumn = $sTableColumn->replace(SQL_ESC, '');
                $arAggrColumn = $sTableColumn->splitToStringsBy('.');
                $sColumn = $this->encodeTableName($arAggrColumn[count($arAggrColumn) - 1])
                                ->__toString();

                if (isset($this->arColumns[$sColumn])) {
                    $this->sSelect = new String($this->sSelect . ' '
                                        . $arCriteria['aggregate'][$sColumn]
                                        . '(' . $sColumn . ') AS '
                                        . SQL_ESC
                                        . $this->sTableName
                                        . '.' . $sColumn
                                        . SQL_ESC
                                        . ',');
                    // parsing the column to int,
                    // because the result is always an int of all aggregate functions!
                    $this->arColumns[$sColumn] = 'int';
                    unset($arOwnColumns[$sColumn]);
                } elseif (!isset($this->arColumns[$sColumn])) {
                    $sTableColumn = new String(array_keys($arCriteria['aggregate'])[0]);
                    $sTableColumn = $sTableColumn->replace(SQL_ESC, '');
                    $arAggrColumn = $sTableColumn->splitToStringsBy('.');
                    $sColumn = $this->encodeTableName($arAggrColumn[count($arAggrColumn) - 1])
                                ->__toString();
                    // if it is a * or something else, give the function also $sColumn value
                    $this->sSelect = new String($this->sSelect . ' '
                                        . $arCriteria['aggregate'][$sColumn]
                                        . '(' . $sColumn . ') AS '
                                        . SQL_ESC
                                        . $this->sTableName
                                        . '.aggregate'
                                        . SQL_ESC
                                        . ',');
                    // add a pseudo column in order for later processing in the fetch mode
                    $this->arColumns['aggregate'] = 'int';
                }
            }

            if (
                array_filled($arCriteria)
                && array_key_exists('columns', $arCriteria)
            ) {
                foreach ($arCriteria['columns'] as $sColumn) {
                    $strColumn = $sColumn instanceof String ? $sColumn : new String($sColumn . '');
                    $this->sSelect = new String($this->sSelect
                                        . ' '
                                        . $strColumn
                                        . ' AS '
                                        . SQL_ESC
                                        . $strColumn->replace(SQL_ESC, '') . SQL_ESC . ',');
                }
            } else {
                foreach ($arOwnColumns as $sColumnName => $sColumnType) {
                    $this->sSelect = new String($this->sSelect
                                        . ' '
                                        . SQL_ESC
                                        . $this->sTableName
                                        . SQL_ESC
                                        .
                                        '.'
                                        . SQL_ESC
                                        . $sColumnName
                                        . SQL_ESC
                                        . ' AS '
                                        . SQL_ESC
                                        . $this->sTableName
                                        . '.'
                                        . $sColumnName
                                        . SQL_ESC . ',');
                }

                $iCounter = 0;
                foreach ($this->arJoin as $sJoin) {
                    if (is_array($sJoin)) {
                        $sJoinName = $this->decodeTableName($sJoin['ftable'])->__toString();
                        $sFtablePrefix = 'table' . $iCounter;
                        $sJoinModel = "App\\Mvc\\Model\\"
                                        . $this->decodeTableName($sJoinName)
                                        . 'Model';
                        $obj = new $sJoinModel();
                        $arColumns = $obj->getColumns();

                        foreach ($arColumns as $sColumn => $sType) {
                            $this->sSelect = new String($this->sSelect
                                                . ' '
                                                . SQL_ESC
                                                . $sFtablePrefix
                                                . SQL_ESC
                                                . '.'
                                                . SQL_ESC
                                                . $sColumn
                                                . SQL_ESC
                                                . ' AS '
                                                . SQL_ESC
                                                . $sFtablePrefix
                                                . '.'
                                                . $sColumn
                                                . SQL_ESC
                                                . ',');
                        }
                        $iCounter++;
                    } else {
                        $sJoinModel = "App\\Mvc\\Model\\"
                                        . $this->decodeTableName($sJoin)
                                        . 'Model';
                        $obj = new $sJoinModel();
                        $arColumns = $obj->getColumns();
                        $sTableName = $obj->getTableName();

                        foreach ($arColumns as $sColumn => $sType) {
                            if (
                                isset($arDistinctField)
                                && $arDistinctField[0]->equals($sJoin)
                                && $arDistinctField[1]->equals($sColumn)
                            ) {
                                continue;
                            }

                            $this->sSelect = new String(
                                                $this->sSelect
                                                . ' '
                                                . SQL_ESC
                                                . $sTableName
                                                . SQL_ESC
                                                . '.'
                                                . SQL_ESC
                                                . $sColumn
                                                . SQL_ESC
                                                . ' AS '
                                                . SQL_ESC
                                                . $sTableName
                                                . '.'
                                                . $sColumn
                                                . SQL_ESC
                                                . ','
                                            );
                        }
                    }
                }
            }

            $this->sSelect = new String($this->sSelect->substring(
                                    0,
                                    $this->sSelect->length - 1)
                                . PHP_EOL
                                . 'FROM '
                                . SQL_ESC
                                . $this->sTableName
                                . SQL_ESC
                                . PHP_EOL);
        }

        public function buildJoin()
        {
            $iCounter = 0;
            $sOwnTable = $this->encodeTableName($this->sTableName)->__toString();

            foreach ($this->arJoin as $sTableName) {
                if (is_array($sTableName)) {
                    $this->sJoin = new String($this->sJoin
                                        . 'inner join '
                                        . SQL_ESC
                                        . $sTableName['ftable']
                                        . SQL_ESC . 'table' . $iCounter
                                        . ' on '
                                        . SQL_ESC
                                        . $sOwnTable
                                        . SQL_ESC
                                        . '.'
                                        . SQL_ESC
                                        . $sTableName['okey']
                                        . SQL_ESC
                                        . ' = '
                                        . SQL_ESC
                                        . 'table'
                                        . $iCounter
                                        . SQL_ESC
                                        . '.'
                                        . SQL_ESC
                                        . $sTableName['fkey']
                                        . SQL_ESC . PHP_EOL);
                    $iCounter++;
                } else {
                    $sTableName = $sTableName->toLower();
                    $sJoinKey = $this->findTableFk($sTableName, $bIsOwnKey);

                    if (empty($sJoinKey)) {
                        // TODO: Wenn es kein eigener Key ist, auch Unterscheidung einbauen evtl.
                        $ar = $this->findTableFKComplex($sTableName);
                        foreach ($ar as $sKey => $sVal) {
                            $sKey = new String($sKey);
                            $arKey = $sKey->splitToStringsBy('.');
                            $sVal = new String($sVal);
                            $arVal = $sVal->splitToStringsBy('.');

                            $this->sJoin = new String($this->sJoin
                                            . 'inner join '
                                            . SQL_ESC
                                            . $sTableName
                                            . SQL_ESC
                                            . ' on '
                                            . SQL_ESC
                                            . $arKey[0]
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . $arKey[1]
                                            . SQL_ESC
                                            . ' = '
                                            . SQL_ESC
                                            . $arVal[0]
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . $arVal[1]
                                            . SQL_ESC
                                            . PHP_EOL);
                        }
                    } else {
                        if ($bIsOwnKey) {
                            $this->sJoin = new String($this->sJoin
                                            . 'inner join '
                                            . SQL_ESC
                                            . $sTableName
                                            . SQL_ESC
                                            . ' on '
                                            . SQL_ESC
                                            . $this->sTableName
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . $sJoinKey
                                            . SQL_ESC
                                            . ' = '
                                            . SQL_ESC
                                            . $sTableName
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . 'id'
                                            . SQL_ESC
                                            . PHP_EOL);
                        } else {
                            $this->sJoin = new String($this->sJoin
                                            . 'inner join '
                                            . SQL_ESC
                                            . $sTableName
                                            . SQL_ESC
                                            . ' on '
                                            . SQL_ESC
                                            . $this->sTableName
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . 'id'
                                            . SQL_ESC
                                            . ' = '
                                            . SQL_ESC
                                            . $sTableName
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . $sJoinKey
                                            . SQL_ESC
                                            . PHP_EOL);
                        }
                    }
                }
            }
        }

        public function buildWhere()
        {
            $this->sWhere = new String(count($this->arWhere) > 0 ? 'WHERE ' : '');

            $arBindValues = array();
            foreach ($this->arWhere as $arActWhere) {
                $arKeys = array_keys($arActWhere);
                $sKey = $arKeys[0];
                $arCriteria = $arActWhere[$sKey];
                $sValue = $arCriteria[self::CRITERIA_VALUE];

                if (
                    $this->sWhere->length > 6
                    && $arCriteria[self::CRITERIA_CONDITION_CONNECTOR] === self::CRITERIA_AND
                ) {
                    $this->sWhere = new String($this->sWhere . PHP_EOL . self::CRITERIA_AND . ' ');
                } elseif (
                    $this->sWhere->length > 6
                    && $arCriteria[self::CRITERIA_CONDITION_CONNECTOR] === self::CRITERIA_OR
                ) {
                    $this->sWhere = new String($this->sWhere . PHP_EOL . self::CRITERIA_OR . ' ');
                }

                // if nothing is set, guess that the programmer means AND connector
                elseif ($this->sWhere->length > 6) {
                    $this->sWhere = new String($this->sWhere . PHP_EOL . self::CRITERIA_AND . ' ');
                }

                switch ($arCriteria[self::CRITERIA_OPERATOR]) {
                    case self::CRITERIA_EQUALS:
                        $this->sWhere = new String($this->sWhere . $sKey . ' = ' . '?');
                        $this->arBindValues[] = array($sKey => $sValue);
                        break;

                    case self::CRITERIA_LESSER_THAN:
                        $this->sWhere = new String($this->sWhere . $sKey . ' < ' . '?');
                        $this->arBindValues[] = array($sKey => $sValue);
                        break;

                    case self::CRITERIA_LESSER_THAN_EQUALS:
                        $this->sWhere = new String($this->sWhere . $sKey . ' <= ' . '?');
                        $this->arBindValues[] = array($sKey => $sValue);
                        break;

                    case self::CRITERIA_GREATER_THAN:
                        $this->sWhere = new String($this->sWhere . $sKey . ' > ' . '?');
                        $this->arBindValues[] = array($sKey => $sValue);
                        break;

                    case self::CRITERIA_GREATER_THAN_EQUALS:
                        $this->sWhere = new String($this->sWhere . $sKey . ' >= ' . '?');
                        $this->arBindValues[] = array($sKey => $sValue);
                        break;

                    case self::CRITERIA_LIKE:
                        $this->sWhere = new String($this->sWhere
                                            . $sKey
                                            . ' LIKE \''
                                            . $arCriteria[self::CRITERIA_VALUE]
                                            . '\'');
                        break;

                    case self::CRITERIA_IN:
                        $this->sWhere = new String($this->sWhere
                                            . $sKey
                                            . ' IN ('
                                            . $arCriteria[self::CRITERIA_VALUE]
                                            . ')');
                        break;
                    case self::CRITERIA_NOT_EQUALS:
                        $this->sWhere = new String($this->sWhere
                                            . $sKey
                                            . ' != '
                                            . $arCriteria[self::CRITERIA_VALUE]);
                        break;
                    case self::CRITERIA_IS_NOT_NULL:
                        $this->sWhere = new String($this->sWhere . $sKey . ' IS NOT NULL');
                        break;
                    case self::CRITERIA_IS_NULL:
                        $this->sWhere = new String($this->sWhere . $sKey . ' IS NULL');
                        break;
                }
            }
            $this->sWhere = new String($this->sWhere . PHP_EOL);
            $this->arWhere = array();
        }

        public function orderBy(
                                String $sTableName,
                                String $sColumName,
                                $sOrientation = self::ORDER_ASC
                                )
        {
            if ($sOrientation instanceof String) {
                $sOrientation = $sOrientation->__toString();
            }

            if ($this->sOrderBy === null) {
                $this->sOrderBy = new String('ORDER BY '
                                    . SQL_ESC
                                    . $this->encodeTableName($sTableName)
                                    . SQL_ESC
                                    . '.'
                                    . SQL_ESC
                                    . $this->encodeTableName($sColumName)
                                    . SQL_ESC
                                    . ($sOrientation === self::ORDER_ASC ? ' ASC' : ' DESC'));
            } else {
                $this->sOrderBy = new String($this->sOrderBy . ', '
                                    . SQL_ESC
                                    . $this->encodeTableName($sTableName)
                                    . SQL_ESC
                                    . '.'
                                    . SQL_ESC
                                    . $this->encodeTableName($sColumName)
                                    . SQL_ESC
                                    . ($sOrientation === self::ORDER_ASC ? ' ASC' : ' DESC'));
            }
        }

        public function buildFetch(array $arCriteria = null)
        {
            $this->buildSelect($arCriteria);
            $this->buildJoin();
            $this->buildWhere();
            $this->sSQL = new String($this->sSelect
                            . $this->sJoin
                            . $this->sWhere
                            . $this->sOrderBy);
            $this->sSQL = new String($this->sSQL->rTrim()->__toString());

            // usual case: no limit is set and semicolon at the end of the query is missing
            if (
                !$this->sSQL->endsWith(';')
                && !isset($arCriteria['limit'])
            ) {
                $this->sSQL = new String($this->sSQL . ';');
            } elseif (
                !$this->sSQL->endsWith(';')
                && isset($arCriteria['limit'])
                && $arCriteria['limit'] > -1
            ) {
                $this->sSQL = new String($this->sSQL
                                . PHP_EOL
                                . 'LIMIT '
                                . $arCriteria['limit']
                                . ';');
            } elseif (
                $this->sSQL->endsWith(';')
                && isset($arCriteria['limit'])
                && $arCriteria['limit'] > -1
            ) {
                $this->sSQL = new String($this->sSQL->substring(0, $this->sSQL->length - 1)
                                . PHP_EOL
                                . 'LIMIT '
                                . $iLimit
                                . ';');
            }
        }

        public function parseColumn(String $sName, $sColumnValue, String $sType)
        {
            $sMethod = 'set' . $this->decodeTableName($sName);
            $sType = $sType->toLower();

            // more foreign keys that references to the same foreign table
            if (
                array_key_exists($sName->__toString(), $this->arKeys)
                && !$sType->equals('int')
            ) {
                $this->$sMethod($sColumnValue);
                $sColumnValue = $sColumnValue->getId();
                $sType = new String('int');
            }

            $sType = $sType->__toString();

            switch ($sType) {
                case 'string':
                    $sColumnValue = empty($sColumnValue) ? '' : $sColumnValue;
                    $str = new String($sColumnValue);
                    $this->$sMethod($str);
                break;
                case 'float':
                    $this->$sMethod((float)$sColumnValue);
                break;
                case 'int':
                    $this->$sMethod((int)$sColumnValue);
                break;
                case 'integer':
                    $this->$sMethod((int)$sColumnValue);
                break;
                case 'double':
                    $this->$sMethod((double)$sColumnValue);
                break;
                case 'boolean':
                    $this->$sMethod((boolean)$sColumnValue);
                break;
                case 'bool':
                    $this->$sMethod((boolean)$sColumnValue);
                break;
                case 'datetime':
                    $this->$sMethod(new Date($sColumnValue));
                break;
                case 'date':
                    $this->$sMethod(new Date($sColumnValue));
                break;
                default:
                    $this->$sMethod($sColumnValue);
                break;
            }
        }

        /** several PreparedStatement method, that binds values for all SQL-Statements like
         * SELECT, INSERT and UPDATE
         * @param $stmt the PDO PreparedSatement (which has alreay called $stmt->prepare())
         * @return void | false on error when no exception should be thrown.
         */
        protected function bindValues($stmt)
        {
            if (count($this->arBindValues) === 0) {
                return;
            }

            $i = 1;
            //TODO: wozu?
            $iArLen = count($this->arBindValues);

            foreach ($this->arBindValues as $arBind) {
                // TODO: Quick-Fix...
                $arKeys = array_keys($arBind);
                $sName = $arKeys[0];
                $sValue = $arBind[$sName];
                $sNameEsc = new String($sName);
                $sNameEsc = $sNameEsc->replace(SQL_ESC, '');
                $arTableColumnPair = $sNameEsc->splitToStringsBy('.');

                if (count($arTableColumnPair) === 2) {
                    $sName = $arTableColumnPair[1]->__toString();
                    if (!$arTableColumnPair[0]->equals($this->sTableName)) {
                        $bFound = false;
                        foreach ($this->arJoin as $sJoin) {
                            $sTable = new String($sJoin . '');
                            if ($sTable->equals($arTableColumnPair[0])) {
                                $sModel = "App\\Mvc\\Model\\"
                                            . $this->decodeTableName($sTable)->__toString()
                                            . 'Model';

                                if (
                                    !class_exists($sModel)
                                    && $this->bThrowsEx
                                ) {
                                    throw new ModelException(
                                        'Model class not found in BaseModel::_bindValues'
                                    );
                                } elseif (
                                    !class_exists($sModel)
                                    && !$this->bThrowsEx
                                ) {
                                    return false;
                                } else {
                                    $bFound = true;
                                    $mdl = new $sModel();
                                    $arColumns = $mdl->getColumns();
                                    $sType = new String($arColumns[$sName]);
                                    $sType = $sType->toLower();
                                    // TODO: Woher aus gejointen Tabellen den Value beziehen???
                                }

                                break;
                            }
                        }

                        if (
                            !$bFound
                            && $this->bThrowsEx
                        ) {
                            throw new ModelException(
                                'Model for table name "'
                                . $arTableColumnPair[0]
                                . '" not found in BaseModel::_bindValues'
                            );
                        } elseif (
                            !$bFound
                            && !$this->bThrowsEx
                        ) {
                            return false;
                        }
                    } else {
                        // could also be an non-existing value for SQL (Sub-)Select e. g.: column = 1
                        if (
                            array_key_exists($arTableColumnPair[1]->__toString(),
                                $this->arColumnData)
                        ) {
                            $sValue = $this->arColumnData[$arTableColumnPair[1]->__toString()];
                            $sType = new String(
                                        $this->arColumns[$arTableColumnPair[1]
                                            ->__toString()]
                                    );
                            $sType = $sType->toLower();
                        }

                        // its only in where clause, but no columnData is set.
                        elseif (array_key_exists($sName, $this->arColumns)) {
                            $sType = new String($this->arColumns[$sName]);
                            $sType = $sType->toLower();
                        }

                        // in any other case
                        else {
                            $sType = new String(gettype($sValue));
                            $sType = $sType->toLower();
                        }
                    }
                } else {
                    // case 1: its just a usual table column
                    if (
                        array_key_exists($sName, $this->arColumns)
                        && isset($sName, $this->arColumns)
                    ) {
                        $sType = new String($this->arColumns[$sName]);
                    }

                    // case 2: its a key column
                    elseif (
                        array_key_exists($sName, $this->arKeys)
                        && isset($sName, $this->arKeys)
                    ) {
                        $sType = new String('int');
                    }

                    // case 3: a non-existing column, nothing to do!
                    else {
                        $sType = new String('undefined');
                        // not neccessary, but nice code style!
                        continue;
                    }

                    $sType = $sType->toLower();
                }

                $sReturnedType = $sValue instanceof String
                                    ? new String('string')
                                    : new String(gettype($sType));
                $sReturnedType = $sReturnedType->toLower();

                if (
                    $sType->equals('string')
                    && !$sValue instanceof String
                ) {
                    throw new \Exception('Not the datatype as expected for '
                        . $sName
                        . '='
                        . $sValue
                        . '('
                        . $sReturnedType
                        . ')!');
                } elseif (
                    $sType->equals('datetime')
                    && !$sValue instanceof \DateTime
                    || $sType->equals('datetime')
                    && !$sValue instanceof Date
                ) {
                    throw new \Exception('Not the datatype as expected for '
                        . $sName
                        . '='
                        . $sValue
                        . '('
                        . $sReturnedType
                        . ')!');
                } elseif (
                    $sType->equals('date')
                    && !$sValue instanceof \DateTime
                ) {
                    throw new \Exception('Not the datatype as expected for '
                        . $sName
                        . '='
                        . $sValue
                        . '('
                        . $sReturnedType
                        . ')!');
                } elseif (
                    $sReturnedType->contains($sType)
                    || $sReturnedType->equals($sType)
                ) {
                    $sFixType = new String(gettype($sValue));
                    $sFixType = $sFixType->toLower();

                    if ($sFixType->contains($sType) || $sFixType->equals($sType)) {
                        throw new \Exception('Not the datatype as expected for '
                            . $sName
                            . '='
                            . $sValue
                            . '('
                            . gettype($sValue)
                            . ')!');
                    }
                }

                $sType = $sType->__toString();

                switch ($sType) {
                    case 'string':
                        $stmt->bindValue($i, $sValue->__toString(), \PDO::PARAM_STR);
                        $i++;
                    break;
                    case 'int':
                        $stmt->bindValue($i, $sValue, \PDO::PARAM_INT);
                        $i++;
                    break;
                    case 'integer':
                        $stmt->bindValue($i, $sValue, \PDO::PARAM_INT);
                        $i++;
                    break;
                    case 'float':
                        $stmt->bindValue($i, $sValue, \PDO::PARAM_STR);
                        $i++;
                    break;
                    case 'double':
                        // Stupid PHP!!!
                        $stmt->bindValue($i, $sValue, \PDO::PARAM_STR);
                        $i++;
                    break;
                    case 'boolean':
                        $stmt->bindValue($i, $sValue ? 1 : 0, \PDO::PARAM_INT);
                        //$stmt->bindValue($i, $sValue, \PDO::PARAM_BOOL);
                        $i++;
                    break;
                    case 'bool':
                        $stmt->bindValue($i, $sValue ? 1 : 0, \PDO::PARAM_INT);
                        //$stmt->bindValue($i, $sValue, \PDO::PARAM_BOOL);
                        $i++;
                    break;
                    case 'datetime':
                        //$stmt->bindValue($i, $sValue->toSqlDate()->__toString(), \PDO::PARAM_STR);
                        $stmt->bindValue($i, $sValue->format('Y-m-d'), \PDO::PARAM_STR);
                        $i++;
                    break;
                    case 'date':
                        $stmt->bindValue($i, $sValue->toSqlDate()->__toString(), \PDO::PARAM_STR);
                        $i++;
                    break;
                }
            }

            $this->arBindValues = array();
        }

        public function bindInsertValues()
        {
            $this->arBindValues = array();
            $this->sSQL = new String('insert into '
                            . SQL_ESC
                            . $this->encodeTableName($this->sTableName)
                            . SQL_ESC . ' ');
            $sParams = new String('(');
            $sValues = new String('(');

            foreach ($this->arColumnData as $sColumnName => $colValue) {
                // non-key columns
                if (
                    array_key_exists($sColumnName, $this->arColumns)
                    && isset($this->arColumns[$sColumnName])
                ) {
                    if (
                        $colValue !== null
                        && $colValue instanceof String
                        && !$colValue->isEmpty()
                    ) {
                        $this->arBindValues[] = array(
                                                SQL_ESC
                                                . $this->sTableName->__toString()
                                                . SQL_ESC
                                                . '.'
                                                . SQL_ESC
                                                . $sColumnName
                                                . SQL_ESC => $colValue
                                                );
                        $sParams = new String($sParams
                                    . SQL_ESC
                                    . $sColumnName
                                    . SQL_ESC
                                    . ', ');
                        $sValues = new String($sValues . '?, ');
                    } elseif (
                        !$colValue instanceof String
                        && isset($colValue)
                    ) {
                        $this->arBindValues[] = array(
                                                    SQL_ESC
                                                    . $this->sTableName->__toString()
                                                    . SQL_ESC
                                                    . '.'
                                                    . SQL_ESC
                                                    . $sColumnName
                                                    . SQL_ESC => $colValue
                                                );
                        $sParams = new String($sParams
                                    . SQL_ESC
                                    . $sColumnName
                                    . SQL_ESC
                                    . ', ');
                        $sValues = new String($sValues . '?, ');
                    }
                }

                // key columns
                elseif (
                    array_key_exists($sColumnName, $this->arKeys)
                    && isset($this->arKeys[$sColumnName])
                ) {
                    $this->arBindValues[] = array(
                                                SQL_ESC
                                                . $this->sTableName->__toString()
                                                . SQL_ESC
                                                . '.'
                                                . SQL_ESC
                                                . $sColumnName
                                                . SQL_ESC => $colValue
                                            );
                    $sParams = new String($sParams . SQL_ESC . $sColumnName . SQL_ESC . ', ');
                    $sValues = new String($sValues . '?, ');
                }
            }
            $sParams = new String($sParams->substring(0, $sParams->length - 2) . ')');
            $sValues = new String($sValues->substring(0, $sValues->length - 2) . ')');
            $this->sSQL = new String($this->sSQL . $sParams . ' values ' . $sValues);
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare($this->sSQL->__toString());

            $this->bindValues($stmt);

            return $stmt;
        }

        public function addWhereClause($arWhere)
        {
            $this->arWhere[] = $arWhere;
        }

        protected function setLastInsertId()
        {
            $this->setId((int)$this->db->getPdo()->lastInsertId());
        }

        public function bindUpdateValues()
        {
            $this->arBindValues = array();
            $this->sSQL = new String('update '
                            . SQL_ESC
                            . $this->encodeTableName($this->sTableName)
                            . SQL_ESC
                            . PHP_EOL);
            $sValues = new String('SET ');

            foreach ($this->arColumns as $sColumnName => $sType) {
                if ($sColumnName === 'id') {
                    continue;
                }

                if (isset($this->arColumnData[$sColumnName])) {
                    $columnData = $this->arColumnData[$sColumnName];
                    if (
                        $columnData !== null
                        && $columnData instanceof String
                        && !$columnData->isEmpty()
                    ) {
                        $this->arBindValues[] = array(
                                                    SQL_ESC
                                                    . $this->sTableName->__toString()
                                                    . SQL_ESC
                                                    . '.'
                                                    . SQL_ESC
                                                    . $sColumnName
                                                    . SQL_ESC => $columnData
                                                );
                        $sValues = new String($sValues
                                    . SQL_ESC
                                    . $sColumnName
                                    . SQL_ESC
                                    . ' = ?, ');
                    } elseif (
                        !$columnData instanceof String
                        && !empty($columnData)
                        || !$columnData instanceof String
                        && is_bool($columnData)
                    ) {
                        $this->arBindValues[] = array(
                                                    SQL_ESC
                                                    . $this->sTableName->__toString()
                                                    . SQL_ESC
                                                    . '.'
                                                    . SQL_ESC
                                                    . $sColumnName
                                                    . SQL_ESC => $columnData
                                                );
                        $sValues = new String($sValues
                                    . SQL_ESC
                                    . $sColumnName
                                    . SQL_ESC
                                    . ' = ?, ');
                    }
                }
            }

            $this->sSQL = new String($this->sSQL
                            . $sValues->substring(0, $sValues->length - 2)
                            . PHP_EOL
                            . 'WHERE '
                            . SQL_ESC
                            . 'id'
                            . SQL_ESC
                            . ' = ?;');
            $this->arBindValues[] =  array(
                                        SQL_ESC
                                        . $this->sTableName->__toString()
                                        . SQL_ESC
                                        . '.'
                                        . SQL_ESC
                                        . 'id'
                                        . SQL_ESC => $this->getId()
                                    );
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare($this->sSQL->__toString());

            $this->bindValues($stmt);

            return $stmt;
        }

        public function save()
        {
            $bInsert = true;
            $iId = $this->getId();

            if (
                $iId
                && $iId > -1
            ) {
                $stmt = $this->bindUpdateValues();
                $bInsert = false;
            }

            if ($bInsert) {
                $stmt = $this->bindInsertValues();
            }

            $bRet = $stmt->execute();

            if (!$bRet && $this->bThrowsEx) {
                throw new ModelException('Data in BaseModel could not be saved: '
                    . $this->db->getPdo()->lastError());
            } elseif (!$bRet && !$this->bThrowsEx) {
                return false;
            } else {
                if ($bInsert) {
                    $this->setLastInsertId();
                }
            }

            return $bRet;
        }

        public function fetch(array $arCriteria = null)
        {
            if ($this->getId() && $this->getId() > -1) {
                $this->arWhere[] = array(
                                    SQL_ESC
                                    . $this->sTableName
                                    . SQL_ESC
                                    . '.'
                                    . SQL_ESC
                                    . 'id'
                                    . SQL_ESC => array(
                                                    BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                                    BaseModel::CRITERIA_VALUE => $this->getId()
                                                )
                                );
            } elseif (array_filled($this->arColumnData)) {
                foreach ($this->arColumnData as $sName => $data) {
                    $this->arWhere[] = array(
                                        SQL_ESC
                                        . $this->sTableName
                                        . SQL_ESC
                                        . '.'
                                        . SQL_ESC
                                        . $sName
                                        . SQL_ESC => array(
                                                        BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                                        BaseModel::CRITERIA_VALUE => $data
                                                    )
                                    );
                }
            }

            $pdo = $this->db->getPdo();
            $this->buildFetch($arCriteria);
            $stmt = $pdo->prepare(
                            $this->sSQL->__toString(),
                            array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL)
                    );
            $this->bindValues($stmt);
            $stmt->execute();
            $arRow = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);
            $stmt->closeCursor();

            if (!$arRow) {
                // nothing found according to the SQL pattern => not in db...
                $this->setId(-1);
                return false;
            }

            $arForeignColumns = array();
            foreach ($arRow as $sKey => $sValue) {
                if ($sValue !== null) {
                    $strKey = new String($sKey);
                    $arSplit = $strKey->splitToStringsBy('.');
                    $sTestTable = $arSplit[0];
                    $sColumnKey = $arSplit[1];
                    $sColumnKeyDecoded = $this->decodeTableName($sColumnKey);

                    if ($sTestTable->equals($this->sTableName)) {
                        $this->parseColumn($sColumnKey,
                                           $sValue,
                                           array_key_exists($sColumnKey->__toString(),
                                                $this->arColumns)
                                            ? new String($this
                                                ->arColumns[$sColumnKey
                                                    ->__toString()])
                                            : new String('int'));
                    } elseif ($sTestTable->matches('/table[0-9]+/')) {
                        $iCounter = 0;
                        $iSearched = (int)$sTestTable->substring(5)->__toString();

                        foreach ($this->arJoin as $arTable) {
                            if (is_array($arTable)) {
                                $sFTable = $arTable['ftable'] instanceof String
                                                ? $arTable['ftable']->__toString()
                                                : $arTable['ftable'];
                                $sOKey = $arTable['okey'] instanceof String
                                            ? $arTable['okey']->__toString()
                                            : $arTable['okey'];

                                if (
                                    $iCounter === $iSearched
                                    && isset($this->arObjects[$sOKey])
                                ) {
                                    $this->arObjects[$sOKey]
                                        ->parseColumn($this->decodeTableName($sColumnKey),
                                                      $sValue,
                                                      new String(
                                                        $this->arObjects[$sOKey]
                                                            ->getColumns()[$sColumnKey->__toString()]
                                                      )
                                                    );
                                    break;
                                } elseif (
                                    $iCounter === $iSearched
                                    && !isset($this->arObjects[$sOKey])
                                ) {
                                    $sMdl = "App\\Mvc\\Model\\"
                                            . $this->decodeTableName($sFTable)
                                            ->__toString()
                                                . 'Model';
                                    $mdl = new $sMdl();
                                    $mdl->parseColumn(
                                            $this->decodeTableName($sColumnKey),
                                            $sValue,
                                            new String(
                                                $mdl->getColumns()[$this
                                                                   ->encodeTableName($sColumnKey)
                                                                   ->__toString()]
                                            )
                                        );
                                    $this->arObjects[$sOKey] = $mdl;
                                    break;
                                }

                                $iCounter++;
                            }
                        }
                    } else {
                        $sKeyName = $sTestTable->__toString();

                        if (!array_key_exists($sKeyName, $this->arObjects)) {
                            $sModelName = "App\\Mvc\\Model\\"
                                            . $this->decodeTableName($sKeyName)
                                            . 'Model';
                            $obj = new $sModelName();
                            $this->arObjects[$sKeyName] = $obj;
                            $arForeignColumns[$sKeyName] = $obj->getColumns();
                        }

                        $this->arObjects[$sKeyName]
                            ->parseColumn(
                                $sColumnKey,
                                $sValue,
                                array_key_exists(
                                    $sColumnKey->__toString(),
                                    $arForeignColumns[$sKeyName])
                                        ? new String(
                                            $arForeignColumns[$sKeyName][$sColumnKey->__toString()]
                                        )
                                        : new String('int')
                            );
                    }
                }
            }
        }

        public function aggregateCount(String $sColumnName)
        {
            $this->sSelect = new String('SELECT count('
                                . SQL_ESC
                                . $this->encodeTableName($sColumnName)
                                . SQL_ESC
                                . ') AS '
                                . SQL_ESC
                                . $sColumnName
                                . SQL_ESC
                                . PHP_EOL
                                . 'FROM '
                                . SQL_ESC
                                . $this->sTableName
                                . SQL_ESC
                                . PHP_EOL);

            $iCnt = 0;
            foreach ($this->arColumnData as $sKey => $sVal) {
                if ($sKey !== 'id') {
                    if ($iCnt === 0) {
                        $this->arWhere[] = array(
                                            SQL_ESC
                                            . $this->sTableName
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . $sKey
                                            . SQL_ESC => array(
                                                BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                                BaseModel::CRITERIA_VALUE => $sVal
                                            )
                                        );
                    } else {
                        $this->arWhere[] = array(
                                            SQL_ESC
                                            . $this->sTableName
                                            . SQL_ESC
                                            . '.'
                                            . SQL_ESC
                                            . $sKey
                                            . SQL_ESC => array(
                                                            BaseModel::CRITERIA_CONDITION_CONNECTOR => BaseModel::CRITERIA_AND,
                                                            BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                                            BaseModel::CRITERIA_VALUE => $sVal
                                                        )
                                        );
                    }

                    $iCnt++;
                }
            }

            $this->buildWhere();
            $this->sSQL = new String($this->sSelect . $this->sWhere->trim() . ';');
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare($this->sSQL->__toString());
            $this->bindValues($stmt);
            $stmt->execute();
            $arRow = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);
            $stmt->closeCursor();

            return $arRow[$sColumnName->__toString()];
        }

        public function resetFetchCursor()
        {
            $this->bCursorFetching = false;
            $this->stmt->closeCursor();
            $this->fetchNext();
        }

        /**
         * @param array $arCriteria
         * this is in addition to fetchNext, if you need the criteria array.
         * @return boolean
         */
        public function fetchFirst(array $arCriteria = null)
        {
            // fetch everything by the given column values
            if (
                !empty($this->arColumnData)
                && !is_array($arCriteria)
                || !empty($this->arColumnData)
                && is_array($arCriteria)
                && !isset($arCriteria['noWhere'])
            ) {
                foreach ($this->arColumnData as $sName => $data) {
                    $this->arWhere[] = array(
                                        SQL_ESC . $this->sTableName . SQL_ESC . '.'
                                        . SQL_ESC
                                        . $sName
                                        . SQL_ESC => array(
                                            BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                            BaseModel::CRITERIA_VALUE => $data,
                                            BaseModel::CRITERIA_CONDITION_CONNECTOR => BaseModel::CRITERIA_AND
                                        )
                                    );
                }
            }

            $pdo = $this->db->getPdo();
            $this->buildFetch($arCriteria);
            $this->stmt = $pdo->prepare(
                            $this->sSQL->__toString(),
                            array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL)
                        );
            $this->bindValues($this->stmt);
            $this->stmt->execute();
            $this->bCursorFetching = true;

            return $this->fetchNext();
        }

        /**
         * fetching without closing the cursor, e. g. for an Iterator
         *
         * @return boolean
         */
        public function fetchNext()
        {
            if (
                $this->getId()
                && $this->getId() > -1
            ) {
                $this->arWhere[] = array(
                                    SQL_ESC
                                    . $this->sTableName
                                    . SQL_ESC
                                    . '.'
                                    . SQL_ESC
                                    . 'id'
                                    . SQL_ESC => array(
                                        BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                        BaseModel::CRITERIA_VALUE => $this->getId()
                                    )
                                );
            } else {
                foreach ($this->arColumnData as $sName => $data) {
                    $this->arWhere[] = array(
                                        SQL_ESC
                                        . $this->sTableName
                                        . SQL_ESC
                                        . '.'
                                        . SQL_ESC
                                        . $sName
                                        . SQL_ESC => array(
                                                BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                                BaseModel::CRITERIA_VALUE => $data
                                        )
                                    );
                }
            }

            if (!$this->bCursorFetching) {
                $pdo = $this->db->getPdo();
                $this->buildFetch();
                $this->stmt = $pdo->prepare(
                                $this->sSQL->__toString(),
                                array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL)
                            );
                $this->bindValues($this->stmt);
                $this->stmt->execute();
                $this->bCursorFetching = true;
            }

            $arRow = $this->stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);

            if (!$arRow) {
                // nothing found according to the SQL pattern => not in db...
                // or no next data set found. In both cases stop iterating now!
                $this->stmt->closeCursor();
                $this->setId(-1);
                return false;
            }

            $arParseModels = array();
            $arForeignColumns = array();
            foreach ($arRow as $sKey => $sValue) {
                $strKey = new String($sKey);
                $arSplit = $strKey->splitToStringsBy('.');
                $sTestTable = $arSplit[0];
                $sColumnKey = $arSplit[1];
                $sColumnKeyDecoded = $this->decodeTableName($sColumnKey);

                if ($sTestTable->equals($this->sTableName)) {
                    $this->parseColumn($sColumnKey,
                        $sValue,
                        array_key_exists($sColumnKey->__toString(), $this->arColumns)
                            ? new String($this->arColumns[$sColumnKey->__toString()])
                            : new String('int'));
                } elseif ($sTestTable->matches('/table[0-9]+/')) {
                    $iCounter = 0;
                    $iSearched = (int)$sTestTable->substring(5)->__toString();

                    foreach ($this->arJoin as $arTable) {
                        if (is_array($arTable)) {
                            $sFTable = $arTable['ftable'] instanceof String
                                            ? $arTable['ftable']->__toString()
                                            : $arTable['ftable'];
                            $sOKey = $arTable['okey'] instanceof String
                                        ? $arTable['okey']->__toString()
                                        : $arTable['okey'];

                            if (
                                $iCounter === $iSearched
                                && isset($this->arObjects[$sOKey])
                            ) {
                                $this->arObjects[$sOKey]
                                    ->parseColumn($this->decodeTableName($sColumnKey),
                                        $sValue,
                                        new String($this->arObjects[$sOKey]
                                            ->getColumns()[$sColumnKey->__toString()]));
                                break;
                            } elseif ($iCounter === $iSearched
                                && !isset($this->arObjects[$sOKey])
                            ) {
                                $sMdl = "App\\Mvc\\Model\\"
                                        . $this->decodeTableName($sFTable)->__toString()
                                        . 'Model';
                                $mdl = new $sMdl();
                                $mdl->parseColumn(
                                    $this->decodeTableName($sColumnKey),
                                    $sValue,
                                    new String($mdl
                                        ->getColumns()[$this
                                            ->encodeTableName($sColumnKey)->__toString()])
                                );
                                $this->arObjects[$sOKey] = $mdl;
                                break;
                            }

                            $iCounter++;
                        }
                    }
                } else {
                    $sKeyName = $sTestTable->__toString();

                    if (!array_key_exists($sKeyName, $arParseModels)) {
                        $sModelName = "App\\Mvc\\Model\\"
                                        . $this->decodeTableName($sKeyName)
                                        . 'Model';
                        $obj = new $sModelName();
                        $arParseModels[$sKeyName] = $obj;
                        $arForeignColumns[$sKeyName] = $obj->getColumns();
                    }

                    $arParseModels[$sKeyName]
                        ->parseColumn(
                            $sColumnKey,
                            $sValue,
                            array_key_exists(
                                $sColumnKey->__toString(),
                                $arForeignColumns[$sKeyName])
                                    ? new String($arForeignColumns[$sKeyName][$sColumnKey
                                        ->__toString()])
                                    : new String('int')
                    );
                }
            }

            $this->arObjects = $arParseModels;
            return true;
        }

        /**
         * @param array = ('iLimit' => -1, 'distinctField' => '')
         *
         * fetches many datasets respective by the maximal limit and/or the set distinctField
         *
         * @return array<BaseModel> obects of the matched datasets
         */
        public function fetchAll(array $arCriteria = null)
        {
            // fetch everything by the given column values
            if (
                !empty($this->arColumnData)
                && !is_array($arCriteria)
                || !empty($this->arColumnData)
                && is_array($arCriteria)
                && !isset($arCriteria['noWhere'])
            ) {
                foreach ($this->arColumnData as $sName => $data) {
                    $this->arWhere[] = array(
                                        SQL_ESC
                                        . $this->sTableName
                                        . SQL_ESC
                                        . '.'
                                        . SQL_ESC
                                        . $sName
                                        . SQL_ESC => array(
                                                        BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                                        BaseModel::CRITERIA_VALUE => $data,
                                                        BaseModel::CRITERIA_CONDITION_CONNECTOR => BaseModel::CRITERIA_AND
                                                    )
                                    );
                }
            }

            $pdo = $this->db->getPdo();
            $this->buildFetch($arCriteria);
            $stmt = $pdo->prepare(
                        $this->sSQL->__toString(),
                        array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL)
                    );
            $this->bindValues($stmt);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);

            if (!$result) {
                return false;
            }

            $sModel = "App\\Mvc\\Model\\"
                        . $this->decodeTableName($this->sTableName)->__toString()
                        . 'Model';
            $arObjs = array();
            while ($result) {
                $mdlObj = new $sModel();
                foreach ($this->arColumns as $sColumnName => $sDataType) {
                    $sKey = $this->sTableName . '.' . $sColumnName;

                    if (
                        array_key_exists($sKey, $result)
                        && ($result[$sKey] !== null
                            && $result[$sKey] !== '')
                    ) {
                        $mdlObj
                            ->parseColumn(
                                new String($sColumnName),
                                $result[$sKey],
                                new String($sDataType)
                        );
                    }
                }

                $arObjsInMdl = &$mdlObj->getObjects();
                $iCounter = 0;
                foreach ($this->arJoin as $sTable) {
                    if (is_array($sTable)) {
                        $sFTable = $sTable['ftable'];
                        $sMdl = "App\\Mvc\\Model\\"
                                    . $this->decodeTableName($sFTable)->__toString()
                                    . 'Model';
                        $sOKey = $sTable['okey'];
                        $sValue = $result['table' . $iCounter];
                        $mdl = new $sMdl();

                        foreach ($mdl->getColumns() as $sColumn => $sType) {
                            $mdl->parseColumn(
                                $this->decodeTableName($sColumn),
                                $result['table' . $iCounter . '.' . $sColumn],
                                new String($sType)
                            );
                        }

                        $arObjsInMdl[$sOKey->__toString()] = $mdl;
                        $iCounter++;
                    } else {
                        $sModelName = $this->decodeTableName($sTable);
                        $sJoinModel = "App\\Mvc\\Model\\" . $sModelName . 'Model';
                        $obj = new $sJoinModel();
                        $arColumns = $obj->getColumns();
                        $sTableName = $obj->getTableName();

                        foreach ($arColumns as $sColumn => $sType) {
                            $sKey = $sTableName . '.' . $sColumn;

                            if (
                                array_key_exists($sKey, $result)
                                && ($result[$sKey] !== null
                                    && $result[$sKey] !== '')
                            ) {
                                $obj->parseColumn(
                                    new String($sColumn),
                                    $result[$sKey],
                                    new String($sType)
                                );
                            }
                        }

                        $sModelSetter = 'set' . $sModelName;
                        $mdlObj->$sModelSetter($obj);
                    }
                }

                $arObjs[] = $mdlObj;
                $result = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);
            }
            $stmt->closeCursor();

            return $arObjs;
        }

        public function delete()
        {
            $id = $this->getId();
            if (
                is_int($id)
                && $id > -1
            ) {
                $this->arWhere[] = array(
                                    SQL_ESC . $this->sTableName
                                    . SQL_ESC
                                    . '.'
                                    . SQL_ESC
                                    . 'id'
                                    . SQL_ESC => array(
                                        self::CRITERIA_OPERATOR => self::CRITERIA_EQUALS,
                                        self::CRITERIA_VALUE => $id
                                    )
                                );
                $this->buildWhere();
                $this->sSQL = new String('DELETE'
                                . PHP_EOL
                                . 'FROM '
                                . SQL_ESC
                                . $this->encodeTableName($this->sTableName)
                                . SQL_ESC
                                . PHP_EOL
                                . $this->sWhere->trim()
                                . ';');
            } else {
                foreach ($this->arColumnData as $sKey => $sValue) {
                    $this->arWhere[] = array(
                                        SQL_ESC
                                        . $this->sTableName
                                        . SQL_ESC
                                        . '.'
                                        . SQL_ESC
                                        . $sKey
                                        . SQL_ESC => array(
                                            self::CRITERIA_OPERATOR => self::CRITERIA_EQUALS,
                                            self::CRITERIA_VALUE => $sValue
                                        )
                                    );
                }

                $this->buildWhere();
                $this->sSQL = new String('DELETE'
                                . PHP_EOL
                                . 'FROM '
                                . SQL_ESC
                                . $this->encodeTableName($this->sTableName)
                                . SQL_ESC
                                . PHP_EOL
                                . $this->sWhere->trim()
                                . ';');
            }

            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare($this->sSQL);
            $this->bindValues($stmt);
            $bRw = $stmt->execute();

            if (
                $this->bThrowsEx
                && !$bRw
            ) {
                throw new \Exception('Data for table '
                    . $this->sTableName->__toString()
                    . ' could not be deleted.');
            }

            return $bRw;
        }

        /**
         * deletes many datasets from the table by the given values
         *
         * @return int the amount of the rows
         */
        public function deleteMany()
        {
            foreach ($this->arColumnData as $sName => $data) {
                $this->arWhere[] = array(
                                    SQL_ESC
                                    . $this->sTableName
                                    . SQL_ESC
                                    . '.'
                                    . SQL_ESC
                                    . $sName
                                    . SQL_ESC => array(
                                                    BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
                                                    BaseModel::CRITERIA_VALUE => $data
                                                )
                                );
            }

            $this->buildWhere();
            $this->sSQL = new String('DELETE FROM '
                            . $this->encodeTableName($this->getTableName())
                            . PHP_EOL
                            . $this->sWhere->trim()
                            . ';');
            $this->sSQL = new String($this->sSQL->substring(0, $this->sSQL->length - 5) . ';');
            $pdo = $this->db->getPdo();
            $stmt = $pdo->prepare($this->sSQL);
            $this->bindValues($stmt);

            return $stmt->execute();
        }

        /**
         * Works like Comparing two strings
         * compares two data objects with each other
         *
         * @param BaseModel $cmpMdl
         * @return 1 on other object or if something is longer/different
         * 			0 on equals
         * 			-1 on smaller
         */
        public function compareTo(BaseModel $cmpMdl)
        {
            $arOwn = $this->arColumnData;
            $arOtherColumn = $cmpMdl->getColumns();
            $arComparsion = array();

            foreach ($arOtherColumn as $sColumnName => $sColumnType) {
                $sActOwnWalker = $arOwn[$this->encodeTableName($sColumnName)->__toString()];
                $sGetMethod = 'get' . $this->decodeTableName($sColumnName);

                switch ($sColumnType) {
                    case 'string':
                        $arComparsion[$sColumnName] = $sActOwnWalker
                                                        ->compareTo($cmpMdl->$sGetMethod());
                    break;
                    case 'float':
                        $arComparsion[$sColumnName] = gmp_cmp(
                                                        $sActOwnWalker,
                                                        $cmpMdl->$sGetMethod()
                                                    );
                    break;
                    case 'int':
                        $arComparsion[$sColumnName] = gmp_cmp(
                                                        $sActOwnWalker,
                                                        $cmpMdl->$sGetMethod()
                                                    );
                    break;
                    case 'integer':
                        $arComparsion[$sColumnName] = gmp_cmp(
                                                        $sActOwnWalker,
                                                        $cmpMdl->$sGetMethod()
                                                    );
                    break;
                    case 'double':
                        $arComparsion[$sColumnName] = gmp_cmp(
                                                        $sActOwnWalker,
                                                        $cmpMdl->$sGetMethod()
                                                    );
                    break;
                    case 'boolean':
                        $arComparsion[$sColumnName] = $sActOwnWalker === $cmpMdl->$sGetMethod()
                                                        ? 0
                                                        : -1;
                    break;
                    case 'bool':
                        $arComparsion[$sColumnName] = $sActOwnWalker === $cmpMdl->$sGetMethod()
                                                        ? 0
                                                        : -1;
                    break;
                    case 'datetime':
                        $dtOther = $cmpMdl->$sGetMethod();

                        if ($sActOwnWalker > $dtOther) {
                            $arComparsion[$sColumnName] = 1;
                        } elseif ($sActOwnWalker < $dtOther) {
                            $arComparsion[$sColumnName] = -1;
                        } else {
                            $arComparsion[$sColumnName] = 0;
                        }
                    break;
                    case 'date':
                        $dtOther = $cmpMdl->$sGetMethod();

                        if ($sActOwnWalker > $dtOther) {
                            $arComparsion[$sColumnName] = 1;
                        } elseif ($sActOwnWalker < $dtOther) {
                            $arComparsion[$sColumnName] = -1;
                        } else {
                            $arComparsion[$sColumnName] = 0;
                        }
                    break;
                }
            }

            return in_array(1, $arComparsion) ? 1 : in_array(-1, $arComparsion) ? -1 : 0;
        }

        public function __call($sName, $argv)
        {
            $sName = new String($sName);

            if ($sName->toLower()->startsWith('join')) {
                $sJoinTable = $sName->substring(4);

                if (count($argv) === 1) {
                    $this->arJoin[] = array(
                                        'okey' => $this->encodeTableName($argv[0]),
                                        'fkey' => 'id',
                                        'ftable' => $this->encodeTableName($sJoinTable)
                                    );
                } elseif (count($argv) >= 1) {
                    $this->arJoin[] = array(
                                        'okey' => $this->encodeTableName($argv[0]),
                                        'fkey' => $this->encodeTableName($argv[1]),
                                        'ftable' => $this->encodeTableName($sJoinTable)
                                    );
                } else {
                    $this->arJoin[] = $this->encodeTableName($sJoinTable);
                }
            } elseif ($sName->toLower()->startsWith('get')) {
                $sColumn = $this->encodeTableName($sName->substring(3))->__toString();

                // more than one foreign key that references to the same foreign table
                if (
                    isset($argv[0])
                    && $argv[0] instanceof String
                    && $argv[0]->toLower()->equals('model')
                ) {
                    return $this->arObjects[$sColumn->__toString()];
                } elseif (
                    isset($argv[0])
                    && strtolower($argv[0]) === 'model'
                ) {
                    return $this->arObjects[$sColumn];
                }

                $bFound = false;

                foreach ($this->arColumnData as $columnName => $columType) {
                    if ($sColumn === $columnName) {
                        $bFound = true;
                        break;
                    }
                }

                if (!$bFound) {
                    // try to test if its a key column
                    $bFound = array_key_exists($sColumn, $this->arKeys);
                } else {
                    // its an usual column
                    return $this->arColumnData[$sColumn];
                }


                if (!$bFound) {
                    // 2nd try not found. It must be an object
                    if (array_key_exists($sColumn, $this->arObjects)) {
                        return $this->arObjects[$sColumn];
                    } else {
                        return null;
                    }
                } else {
                    // 2nd try passed: It must be a key column
                    if (array_key_exists($sColumn, $this->arKeys)) {
                        return array_key_exists(
                            $sColumn,
                            $this->arColumnData)
                                ? $this->arColumnData[$sColumn]
                                : null;
                    } else {
                        return null;
                    }
                }
            } elseif ($sName->toLower()->startsWith('set')) {
                $sArKey = $this->encodeTableName($sName->substring(3))->__toString();

                if (is_float($argv[0])) {
                    $value = (float)$argv[0];
                    $this->arColumnData[$sArKey] = $value;
                } elseif (is_int($argv[0])) {
                    $value = (int)$argv[0];
                    $this->arColumnData[$sArKey] = $value;
                } elseif (is_string($argv[0])) {
                    $value = new String($argv[0]);
                    $this->arColumnData[$sArKey] = $value;
                } elseif (is_double($argv[0])) {
                    $value = (double)$argv[0];
                    $this->arColumnData[$sArKey] = $value;
                } elseif (is_bool($argv[0])) {
                    $value = (boolean)$argv[0];
                    $this->arColumnData[$sArKey] = $value;
                } elseif ($argv[0] instanceof String) {
                    $value = $argv[0];
                    $this->arColumnData[$sArKey] = $value;
                } elseif ($argv[0] instanceof BaseModel) {
                    $value = $argv[0];

                    // check if its already set... Then it must be an one to many relationship...
                    if (!array_key_exists($sArKey, $this->arObjects)) {
                        $this->arObjects[$sArKey] = $value;
                    } elseif (
                        array_key_exists($sArKey, $this->arObjects)
                        && !is_array($this->arObjects[$sArKey])
                    ) {
                        $sOldVal = $this->arObjects[$sArKey];
                        $this->arObjects[$sArKey] = array($sOldVal, $value);
                    } else {
                        $this->arObjects[$sArKey][] = $value;
                    }
                } else {
                    // if everything is unknown,
                    // set it as an usual model column,
                    // which does not already exists...
                    $value = $argv[0];
                    $this->arColumnData[$sArKey] = $value;
                }
            } else {
                throw new \Exception('Method not defined!');
            }
        }
    }
}
