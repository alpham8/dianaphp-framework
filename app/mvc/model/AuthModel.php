<?php
require_once_file(EPHP_CORE . 'Persistence/Sql/BaseModel.php');
require_once_file(EPHP_CORE . 'Util/Authentification/AuthList.php');

class AuthModel extends BaseModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function load()
	{
		$sSQL = <<<SQL
select `usergroups`.`name` as `group`, `actions`.`name` as `action`, `profile`.`email` as `user`, `allowed`
from profile
inner join usergroups on usergroups.id = profile.group_id
inner join group_rights on usergroups.id = group_rights.group_id
inner join actions on group_rights.action_id = actions.id
order by profile.group_id;
SQL;
		$arRightList = array();

		$pdo = $this->_db->getPdo();
		$stmt = $pdo->prepare($sSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$arRow = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);

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
				$arRow = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
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
?>
