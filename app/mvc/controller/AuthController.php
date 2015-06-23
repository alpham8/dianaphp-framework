<?php
require_once_file(ROOT_PATH . 'app/src/Clanscript20Controller.php');
require_once_file(EPHP_MODEL . 'ProfileModel.php');
require_once_file(EPHP_MODEL . 'UsergroupsModel.php');
require_once_file(EPHP_MODULES . 'phpass/PasswordHash.php');

class AuthController extends Clanscript20Controller
{
	const ITERATION_CNT = 10;

	public function __construct()
	{
		parent::__construct();
	}

	public function doRegister()
	{
		$hasher = new PasswordHash(self::ITERATION_CNT, false);

		$regMdl = new ProfileModel();
		$req = $this->_request;
		$sCaptcha = $req->getParam(new String('tfCaptcha'));

		if (checkstring($sCaptcha) && $sCaptcha->equals($this->_session->get(new String('sCaptcha'))))
		{
			// TODO: Captcha fertig machen
			$this->_session->remove(new String('sCaptcha'));
			$regMdl->setEmail($req->getParam(new String('tfEmail')));
			$regMdl->setPassword($hasher->HashPassword($req->getParam(new String('tfPassword'))->__toString()));
			$regMdl->setName($req->getParam(new String('tfName')));
			$regMdl->setSurname($req->getParam(new String('tfSurname')));
			$regMdl->setPlayerName($req->getParam(new String('tfPlayerName')));
			$regMdl->setStreet($req->getParam(new String('tfStreet')));
			$regMdl->setHomeno($req->getParam(new String('tfHomeNo')));

			$groupMdl = new UsergroupsModel();
			$groupMdl->setName(new String('users'));
			$groupMdl->fetch();
			$regMdl->setGroupId((int)$groupMdl->getId());

			$regMdl->setPostalcode($req->getParam(new String('tfPostalcode')));
			$regMdl->setHomearea($req->getParam(new String('tfHomeArea')));

			$bRet = $regMdl->save();
			$this->_session->set(new String('registeredsuccessfull'), new String($bRet === true ? 'true' : 'false'));
		}

		else
		{
			$sMessageTitleKey = new String('sMessageTitle');
			$sMessageTitle = $this->_session->set($sMessageTitleKey, new String('Registrierung Fehler'));

			$sMessageKey = new String('sMessage');
			$sMessage = $this->_session->set($sMessageKey, new String('Das Captcha wurde nicht korrekt ausgefuellt.'));
		}

		Routes::redirect(new String('index'), new String('news'), false);
	}

	public function doLogin()
	{
		$req = $this->_request;
		$profileMdl = new ProfileModel();
		$profileMdl->setEmail($req->getParam(new String('tfLoginEmail')));
		$sPass = $req->getParam(new String('tfLoginPassword'));
		$profileMdl->joinUsergroups();
		$profileMdl->fetch();

		$hasher = new PasswordHash(self::ITERATION_CNT, false);

		if ($hasher->CheckPassword($sPass->__toString(), $profileMdl->getPassword()->__toString()))
		{
			$this->_session->set(new String('group'), $profileMdl->getUsergroups()->getName());
			$this->_session->set(new String('group_id'), new String($profileMdl->getGroupId() . ''));
			$this->_session->set(new String('user'), $profileMdl->getEmail());
			$this->_session->set(new String('profile_id'), new String($profileMdl->getId() . ''));
		}

		Routes::redirect(new String('index'), new String('news'), false);
	}

	public function doLogout()
	{
		$this->_session->destroy();
		Routes::redirect(new String('index'), new String('news'), false);
	}
}
?>
