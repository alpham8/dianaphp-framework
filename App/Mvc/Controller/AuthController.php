<?php
namespace App\Mvc\Controller
{
	use Diana\Core\Std\String;
	use Diana\Core\Std\Date;
	use Diana\Core\Std\Http\Request;
	use Diana\Core\Std\Http\Response;
	use Diana\Core\Mvc\Routes;
	use Diana\Core\Util\Authentification\Session;
	use App\Src\Clanscript20Controller;
	use App\Mvc\Model\ProfileModel;
	use App\Mvc\Model\ProfileActivationModel;
	use App\Mvc\Model\UsergroupsModel;
	use Diana\Core\Std\Email;
	use App\Src\EventLogger;
 	require_once_file(DIANA_MODULES . 'phpass/PasswordHash.php');

	class AuthController extends Clanscript20Controller
	{
		const ITERATION_CNT = 10;

		public function __construct()
		{
			parent::__construct();
		}

		public function doRegister()
		{
			$req = $this->_request;
			$sCaptcha = $req->getParam(new String('tfCaptcha'));
			$sCaptcha = $sCaptcha->toUpper();

			if (checkstring($sCaptcha) && $sCaptcha->equals($this->_session->get(new String('sCaptcha'))))
			{
				$regMdl = new ProfileModel();
				$hasher = new \PasswordHash(self::ITERATION_CNT, false);
				$this->_session->remove(new String('sCaptcha'));
				$regMdl->beginTransaction();

				try
				{
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

					do
					{
						$sToken = new String(base64_encode($sToken . uniqid(rand(0,  PHP_INT_MAX) . rand(0, PHP_INT_MAX))));
						$sToken = $sToken->trim();
						$prfActMdl = new ProfileActivationModel();
						$prfActMdl->setToken($sToken);
						$prfActMdl->fetch();
					} while ($prfActMdl->getId() > 0);

					$prfActMdl = new ProfileActivationModel();
					$prfActMdl->setProfileId($regMdl->getId());
					$prfActMdl->setToken($sToken);
					$dtTomorrow2 = new Date();
					$dtTomorrow2->modify('+2 days');
					$prfActMdl->setValid_until($dtTomorrow2);
					$prfActMdl->save();

					EventLogger::newUser($regMdl);

					$email = new Email();
					$email->setFrom(new String(MAIL_FROM));
					$email->setRcpt($regMdl->getEmail());
					$email->setSubject(new String('Register gaming-veterans.de'));
					$sBody = new String('Herzlich Willkommen bei ' . CLAN_NAME . ",\r\n\r\n"
										. "um Ihre Registrierung abzuschließen klicken Sie bitte auf folgenden Link:\r\n"
										. $this->_request->getBaseUri() . 'auth/regComplete/token%3D' . $sToken . "\r\n");
					$email->setBody($sBody);
					$bRet = $email->send();

					if ($bRet)
					{
						$regMdl->commit();
						$this->_session->set(new String('registeredsuccessfull'), new String($bRet === true ? 'true' : 'false'));
					}

					else
					{
						$regMdl->rollBack();
						throw new \Exception('Transaktion ging nicht ordentlich zu Ende nach dem E-Mail senden.');
					}

				}

				catch (\Exception $ex)
				{
					$regMdl->rollBack();
					$sMessageTitleKey = new String('sMessageTitle');
					$sMessageTitle = $this->_session->set($sMessageTitleKey, new String('Registrierung Fehler'));

					$sMessageKey = new String('sMessage');
					$sMessage = $this->_session->set($sMessageKey, new String('Fehler beim Speichern: ' . nl2br($ex->getMessage())));
				}
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

		public function regComplete()
		{
			$sToken = $this->_request->getParam(new String('token'));

			if (checkstring($sToken))
			{
				$prfActMdl = new ProfileActivationModel();
				$prfActMdl->setToken($sToken);
				$prfActMdl->fetch();

				if ($prfActMdl->getId() > 0)
				{
					$bRw = $prfActMdl->delete();
					$this->_session->set(new String('activatedsuccessfull'), new String($bRw ? 'true' : 'false'));
				}

				else
				{
					$this->_session->set(new String('sMessageTitle'), new String('Aktivierung Fehler'));
					$this->_session->set(new String('sMessage'), new String('Token nicht gefunden.'));
				}
			}

			else
			{
				$this->_session->set(new String('sMessageTitle'), new String('Aktivierung Fehler'));
				$this->_session->set(new String('sMessage'), new String('kein Token gesetzt.'));
			}

			Routes::redirect(new String('index'), new String('news'), false);
		}

		protected function _checkActivationExpired(ProfileActivationModel $prfActMdl)
		{
			$dtNow = new Date();

			return $prfActMdl->getValid_until() >= $dtNow;
		}

		protected function _dltExpiredProfile(ProfileModel $profileMdl, ProfileActivationModel $prfActMdl)
		{
			$profileMdl->beginTransaction();

			try
			{
				$profileMdl->delete();
				$prfActMdl->delete();
				$profileMdl->commit();
			}

			catch (\Exception $ex)
			{
				$profileMdl->rollBack();
				EventLogger::logWarning(new String('user profile with E-Mail "' . $profileMdl->getEmail()
												   . '" and player name "' . $profileMdl->getPlayerName()
												   . '" was not able to delete.'));
			}
		}

		public function doLogin()
		{
			$req = $this->_request;
			$profileMdl = new ProfileModel();
			$profileMdl->setEmail($req->getParam(new String('tfLoginEmail')));
			$sPass = $req->getParam(new String('tfLoginPassword'));
			$profileMdl->joinUsergroups();
			$profileMdl->fetch();

			if ($profileMdl->getId() > 0)
			{
				$prfActMdl = new ProfileActivationModel();
				$prfActMdl->setProfileId($profileMdl->getId());
				$prfActMdl->fetch();

				if ($prfActMdl->getId() > 0)
				{
					if (!$this->_checkActivationExpired($prfActMdl))
					{
						$this->_dltExpiredProfile($profileMdl, $prfActMdl);
						$this->_session->set(new String('sMessageTitle'), new String('Profil Aktivierung abgelaufen'));
						$this->_session->set(new String('sMessage'), new String('Ihre Profil Aktivierung ist abgelaufen. Das Profil wurde unwiederruflich gel&ouml;scht!'));
					}

					else
					{
						$this->_session->set(new String('sMessageTitle'), new String('Profil Aktivierung'));
						$this->_session->set(new String('sMessage'), new String('Profil muss noch aktiviert werden!'));
					}
				}

				else
				{
					if (EventLogger::getLoginTrys($profileMdl) <= MAX_LOGIN_TRYS)
					{
						$hasher = new \PasswordHash(self::ITERATION_CNT, false);

						if ($hasher->CheckPassword($sPass->__toString(), $profileMdl->getPassword()->__toString()))
						{
							$this->_session->set(new String('group'), $profileMdl->getUsergroups()->getName());
							$this->_session->set(new String('group_id'), new String($profileMdl->getGroupId() . ''));
							$this->_session->set(new String('user'), $profileMdl->getEmail());
							$this->_session->set(new String('profile_id'), new String($profileMdl->getId() . ''));
							EventLogger::setLastLogin($profileMdl);
						}

						else
						{
							EventLogger::loginTry($profileMdl);

							if (EventLogger::getLoginTrys($profileMdl) > MAX_LOGIN_TRYS)
							{
								$this->_session->set(new String('sMessageTitle'), new String('Benutzername/Passwort falsch'));
								$this->_session->set(new String('sMessage'), new String('Ihr Profil ist f&uuml;r 10 Minuten gesperrt.'));
							}

							else
							{
								$this->_session->set(new String('sMessageTitle'), new String('Benutzername/Passwort falsch'));
								$this->_session->set(new String('sMessage'), new String('Benutzername oder Passwort falsch. Bitte versuchen Sie es erneut.'));
							}
						}
					}

					else
					{
						$this->_session->set(new String('sMessageTitle'), new String('Benutzername/Passwort falsch'));
						$this->_session->set(new String('sMessage'), new String('Ihr Profil ist f&uuml;r 10 Minuten gesperrt.'));
					}
				}
			}

			Routes::redirect(new String('index'), new String('news'), false);
		}

		public function doLogout()
		{
			$this->_session->destroy();
			Routes::redirect(new String('index'), new String('news'), false);
		}
	}
}
?>
