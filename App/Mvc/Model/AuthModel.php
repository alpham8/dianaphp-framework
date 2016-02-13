<?php
namespace App\Mvc\Model
{
	use Diana\Core\Persistence\Sql\BaseModel;
	use Diana\Core\Util\Authentification\AuthList;


	class AuthModel extends BaseModel
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function load()
		{
			$sSQL = 'select ' . SQL_ESC . 'usergroups' . SQL_ESC . '.' . SQL_ESC . 'name' . SQL_ESC . ' as ' . SQL_ESC . 'group' . SQL_ESC . ', '
					. SQL_ESC . 'actions' . SQL_ESC . '.' . SQL_ESC . 'name' . SQL_ESC . ' as ' . SQL_ESC . 'action' . SQL_ESC . ', '
					. SQL_ESC . 'profile' . SQL_ESC . '.' . SQL_ESC . 'email' . SQL_ESC . ' as ' . SQL_ESC . 'user' . SQL_ESC . ', ' . SQL_ESC . 'allowed' . SQL_ESC . PHP_EOL;
			$sSQL .= <<<SQL
from profile
right outer join usergroups on usergroups.id = profile.group_id
inner join group_rights on usergroups.id = group_rights.group_id
inner join actions on group_rights.action_id = actions.id
order by usergroups.id asc;
SQL;
			$arRightList = array();

			$pdo = $this->_db->getPdo();
			$stmt = $pdo->prepare($sSQL, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
			$stmt->execute();
			$arRow = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);

			$auth = new AuthList();
			if ($arRow)
			{
				$sGroup = $arRow['group'];
				$sGroupNew = $sGroup;
				$arActRights = array();
				$arActUsers = array();
			}

			while ($arRow)
			{
				while ($sGroup === $sGroupNew && $arRow)
				{
					if ($arRow['allowed'] == 0)
					{
						$arActRights[$arRow['action']] = AuthList::ACTION_DENY;
					}

					else
					{
						$arActRights[$arRow['action']] = AuthList::ACTION_ACCEPT;
					}

					if (!in_array($arRow['user'], $arActUsers) && $sGroup === $sGroupNew)
					{
						$arActUsers[] = $arRow['user'];
					}

					// read after and set new group
					$arRow = $stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT);
					$sGroupNew = $arRow['group'];
				}

				$auth->addGroup($sGroup, $arActRights);

				foreach ($arActUsers as $sActUser)
				{
					$auth->setPrimaryUserGroup($sGroup, $sActUser);
				}

				$arActRights = array();
				$arActUsers = array();
				$sGroup = $sGroupNew;
			}
			$stmt->closeCursor();

			return $auth;
		}
	}
}
?>
