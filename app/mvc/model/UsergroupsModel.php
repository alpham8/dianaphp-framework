<?php
require_once_file(EPHP_CORE . 'Util/Authentification/AuthList.php');

class UsergroupsModel extends BaseModel
{
	//protected $_iId;
	//protected $_sName;

	public function __construct()
	{
		parent::__construct();
		$this->_sTableName = new String('usergroups');
		$this->_arColumns = array(
									'id' => 'int',
									'name' => 'string'
								 );
		$this->_arKeys = array();
	}

	//public function fetch()
	//{
	//	$iId = $this->getId();
	//	if ($iId && $iId > 0)
	//	{
	//		$iId = $this->getId();
	//		$this->_arWhere[] = array(SQL_ESC . 'usergroups' . SQL_ESC . '.' . SQL_ESC . 'id' . SQL_ESC => array(
	//																								   self::CRITERIA_OPERATOR => self::CRITERIA_EQUALS,
	//																								   self::CRITERIA_VALUE => $iId
	//																							));
	//	}
	//	else
	//	{
	//		$this->_arWhere[] = array(SQL_ESC . 'usergroups' . SQL_ESC . '.' . SQL_ESC . 'name' . SQL_ESC => array(
	//																								   self::CRITERIA_OPERATOR => self::CRITERIA_EQUALS,
	//																								   self::CRITERIA_VALUE => $this->getName()
	//																							));
	//	}
	//
	//	$this->buildFetch();
	//	$pdo = $this->_db->getPdo();
	//	$stmt = $pdo->prepare($this->_sSQL);
	//	$this->_bindValues($stmt);
	//	$stmt->execute();
	//	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	//	$this->setId((int)$result['usergroups.id']);
	//	$this->setName(new String($result['usergroups.name']));
	//}
}
?>
