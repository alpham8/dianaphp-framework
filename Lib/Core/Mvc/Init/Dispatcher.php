<?php
namespace Diana\Core\Mvc\Init
{
	use Diana\Core\Std\String;
	use Diana\Core\Std\Http\Request;
	use Diana\Core\Std\Http\Response;
	use Diana\Core\Mvc\Routes;
	use Diana\Core\Mvc\Init\WebRequestGlueInterface;
	use Diana\Core\Mvc\Init\DefaultWebRequestGlue;

	abstract class Dispatcher
	{
		protected static $_sController;
		protected static $_sAction;
		protected static $_webRequestGlue;

		private static function _parseUri(String $sRequestUri)
		{
			if ($sRequestUri->endsWith(APP_ROOT))
			{
				$sRequestUri = new String($sRequestUri->__toString() . 'index/index');
			}

			$sAppRequest = Routes::getRoute($sRequestUri)->substring($sRequestUri->indexOf(APP_ROOT) + strlen(APP_ROOT));
			$arApp = $sAppRequest->splitToStringsBy('/');
			self::$_sController = $arApp[0];
			$arAction = $arApp[1]->splitToStringsBy('?');
			self::$_sAction = $arAction[0];
		}

		public static function init(String $sRequestUri)
		{
			self::_parseUri($sRequestUri);
		}

		private static function _doBaseStuff()
		{
			$sFullCtlName = "App\\Mvc\\Controller\\" . ucfirst(self::$_sController . 'Controller');
			$request = new Request();
			$response = new Response();

			if (empty(self::$_webRequestGlue))
			{
				self::$_webRequestGlue = new DefaultWebRequestGlue($request, $response);
			}

			$controller = new $sFullCtlName();

			try
			{
				$controller->setRequest($request);
				$controller->setResponse($response);
				$controller->setControllerName(self::$_sController);
				$controller->setActionName(self::$_sAction);
				$bRet = $controller->preExec();

				if ($bRet)
				{
					$sLocalAction = self::$_sAction->__toString();
					$controller->$sLocalAction();
				}

				$controller->afterExec();
			}
			catch (\Exception $ex)
			{
				$controller->_errorHandler($ex);
			}

			if (!headers_sent())
			{
				// send the response if not already done before
				$this->_webRequestGlue->sendResponse();
			}
		}

		public static function dispatch()
		{
			self::_doBaseStuff();
			exit(0);
		}

		public static function redirect(String $sController, String $sAction)
		{
			self::$_sController = $sController;
			self::$_sAction = $sAction;
			self::_doBaseStuff();
			exit(0);
		}

		public static function setWebRequestGlue(WebRequestGlueInterface $webRequestGlue)
		{
			self::$_webRequestGlue = $webRequestGlue;
		}
	}
}
?>
