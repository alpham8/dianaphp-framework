<?php
namespace App\Src
{
	use Diana\Core\Std\String;
	use Diana\Core\Std\Http\Request;
	use Diana\Core\Std\Http\Response;
	use Diana\Core\Mvc\BaseController;
	use Diana\Core\Util\Authentification\Session;
	use Diana\Core\Persistence\Sql\BaseModel;
	use App\Mvc\Model\AuthModel;
	use App\Mvc\Model\SliderContentModel;
	use App\Mvc\Model\SiteModel;
	use App\Mvc\Model\SocialButtonModel;
	use App\Mvc\Model\TranslationModel;
	use App\Mvc\Model\ProfileModel;
	use App\Src\BootstrapPaginator;

	class Clanscript20Controller extends BaseController
	{
		protected $_auth;
		protected $_session;
		const PAGINATION_ITEMS = 20;

		public function __construct()
		{
			parent::__construct();
			$this->_session = Session::getInstance();
			$sGroupKey = new String('group');

			if (!$this->_session->get($sGroupKey))
			{
				$sGroupVal = new String('public');
				$this->_session->set($sGroupKey, $sGroupVal);
				$sUserKey = new String('user');
				$sUserVal = new String('public');
				$this->_session->set($sUserKey, $sUserVal);
			}
			$this->_setTemplate(new String(DIANA_TEMPLATES . 'standard.phtml'));
		}

		public function preExec()
		{
			$this->_response->setDatatype(new String('html'));
			$authMdl = new AuthModel();
			$this->_auth = $authMdl->load();

			$sGroupKey = new String('group');
			$sUserKey = new String('user');
			$this->_view->sGroup = $this->_session->get($sGroupKey);
			$this->_view->sUser = $this->_session->get($sUserKey);


			if ($this->_sControllerName->equals('site'))
			{
				// blindes erlauben von Custom-Seiten
				return true;
			}

			$sActionName = $this->_sControllerName->toLower() . '->' . $this->_sActionName->toLower();

			if ($this->_auth->isAllowed($this->_session->get($sUserKey), $sActionName))
			{
				return true;
			}

			$this->_sView = new String(ROOT_PATH . 'app/mvc/views/notallowed.phtml');

			return false;
		}

		protected function _fetchFrontendUser()
		{
			// BEGIN User
			$sUser = $this->_session->get(new String('user'));
			$sGroup = $this->_session->get(new String('group'));

			if (!$sGroup->equals('public') && !$sUser->equals('public'))
			{
				$profileMdl = new ProfileModel();
				$profileMdl->setEmail($sUser);
				$profileMdl->joinUsergroups();
				$profileMdl->fetch();
				$sName = $profileMdl->getName();
				$sSurname = $profileMdl->getSurname();

				if ($sName != null && $sName instanceof String && $sSurname != null && $sSurname instanceof String)
				{
					$this->_view->sName = $sName;
					$this->_view->sSurname = $sSurname;
					$this->_view->iGroupId = $profileMdl->getUsergroups()->getId();
				}
			}

			else
			{
				$profileMdl = new ProfileModel();
				$profileMdl->setId(1);
				$profileMdl->fetch();
			}

			return $profileMdl;
			// END User
		}

		protected function _paginateView($sModel, String $sController, String $sAction, array $arAdditonalParams = null)
		{
			$sFirstEntryId = $this->_request->getParam(new String('firstentryid'));
			$sLastEntryId = $this->_request->getParam(new String('lastentryid'));

			$mdl = self::_crtBasePaginationMdl($sModel);

			if ($sLastEntryId !== null && $sLastEntryId instanceof String && !$sLastEntryId->isEmpty() && $sFirstEntryId === null)
			{
				$mdl->addWhereClause(array(
												SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
												. SQL_ESC . 'id' . SQL_ESC => array(
																						BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
																						BaseModel::CRITERIA_VALUE => (int)$sLastEntryId->__toString()
																					)
												));
			}

			elseif ($sLastEntryId === null && $sFirstEntryId !== null && $sFirstEntryId instanceof String && !$sFirstEntryId->isEmpty())
			{
				$mdl->addWhereClause(array(
												SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
												. SQL_ESC . 'id' . SQL_ESC => array(
																						BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
																						BaseModel::CRITERIA_VALUE => (int)$sFirstEntryId->__toString()
																					)
												));
			}

			elseif ($sLastEntryId !== null && $sLastEntryId instanceof String && !$sLastEntryId->isEmpty()
					&& $sFirstEntryId instanceof String && !$sFirstEntryId->isEmpty())
			{
				$mdl->addWhereClause(array(
												SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
												. SQL_ESC . 'id' . SQL_ESC => array(
																						BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
																						BaseModel::CRITERIA_VALUE => (int)$sFirstEntryId->__toString()
																					)
												));
			}

			else
			{
				$mdl->addWhereClause(array(
												SQL_ESC . $mdl->getTableName() . SQL_ESC . '.'
												. SQL_ESC . 'id' . SQL_ESC => array(
																						BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_GREATER_THAN_EQUALS,
																						BaseModel::CRITERIA_VALUE => 1
																					)
												));
			}

			$this->_view->arEntries = $mdl->fetchAll(array('limit' => self::PAGINATION_ITEMS));

			if ($this->_view->arEntries)
			{
				$mdl = self::_crtBasePaginationMdl($sModel);
				$this->_view->paginator = new BootstrapPaginator($mdl, $this->_view->arEntries, self::PAGINATION_ITEMS, $this->_view, $sController, $sAction, $arAdditonalParams);
			}
		}

		protected function _loadSlider()
		{
			$sliderMdl = new SliderContentModel();
			$this->_view->arSlider = $sliderMdl->fetchAll();
		}

		protected function _loadSites()
		{
			$siteMdl = new SiteModel();
			$siteMdl->joinLanguage();
			$sLocale = new String(\Locale::getDefault());
			$sLocale = $sLocale->substring(0, 2);
			$siteMdl->addWhereClause(array(SQL_ESC . 'language' . SQL_ESC . '.' . SQL_ESC . 'lang' . SQL_ESC =>
												array(
														BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
														BaseModel::CRITERIA_VALUE => $sLocale)));
			$siteMdl->orderBy($siteMdl->getTableName(), new String('sorting_order'));
			$this->_view->arSites = $siteMdl->fetchAll();
		}

		protected function _loadSocialButtons()
		{
			$socialMdl = new SocialButtonModel();
			$this->_view->arSocialBtns = $socialMdl->fetchAll();
		}

		protected function _parseLangCookie()
		{
			if (isset($_COOKIE['lang']) && $_COOKIE !== '')
			{
				// try to parse Language...
				$sMethod = 'get' . $_COOKIE['lang'];
				$transMdl = new TranslationModel();
				$transMdl->setId(1);
				$transMdl->fetch();
				$sResult = $transMdl->$sMethod();

				if (checkstring($sResult))
				{
					\Locale::setDefault($_COOKIE['lang']);
				}

				else
				{
					// silent Fallback if Langauge does not exist
					\Locale::setDefault('en_GB');
				}
			}
		}

		private static function _crtBasePaginationMdl(&$sModel)
		{
			if (is_array($sModel))
			{
				$sModelEsc = "App\\Mvc\\Model\\" . $sModel['model'];
				$mdl = new $sModelEsc();

				if (array_key_exists('joinModel', $sModel))
				{
					foreach ($sModel['joinModel'] as $sModelJoin)
					{
						$sJoin = 'join' . $sModelJoin->ucFirst();
						$mdl->$sJoin();
					}
				}

				if (array_key_exists('whereClause', $sModel))
				{
					foreach($sModel['whereClause'] as $arWhere)
					{
						$mdl->addWhereClause($arWhere);
					}
				}

				if (array_key_exists('orderBy', $sModel))
				{
					$mdl->orderBy($sModel['orderBy'][0], $sModel['orderBy'][1], BaseModel::ORDER_DESC);
				}
			}

			else
			{
				$sModelLocal = "App\\Mvc\\Model\\" . $sModel;
				$mdl = new $sModelLocal();
			}

			return $mdl;
		}
	}
}
?>
