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
        protected static $sController;
        protected static $sAction;
        protected static $webRequestGlue;

        private static function parseUri(String $sRequestUri)
        {
            if ($sRequestUri->endsWith(APP_ROOT)) {
                $sRequestUri = new String($sRequestUri->__toString() . 'index/index');
            }

            $sAppRequest = Routes::getRoute($sRequestUri)
                            ->substring($sRequestUri->indexOf(APP_ROOT) + strlen(APP_ROOT));
            $arApp = $sAppRequest->splitToStringsBy('/');
            self::$sController = $arApp[0];
            $arAction = $arApp[1]->splitToStringsBy('?');
            self::$sAction = $arAction[0];
        }

        public static function init(String $sRequestUri)
        {
            self::parseUri($sRequestUri);
        }

        private static function _doBaseStuff()
        {
            $sFullCtlName = "App\\Mvc\\Controller\\" . ucfirst(self::$sController . 'Controller');
            $request = new Request();
            $response = new Response();

            if (empty(self::$webRequestGlue)) {
                self::$webRequestGlue = new DefaultWebRequestGlue($request, $response);
            }

            $controller = new $sFullCtlName();

            try {
                $controller->setRequest($request);
                $controller->setResponse($response);
                $controller->setControllerName(self::$sController);
                $controller->setActionName(self::$sAction);
                $bRet = $controller->preExec();

                if ($bRet) {
                    $sLocalAction = self::$sAction->__toString();
                    $controller->$sLocalAction();
                }

                $controller->afterExec();
            } catch (\Exception $ex) {
                $controller->errorHandler($ex);
            }

            if (!headers_sent()) {
                // send the response if not already done before
                $this->webRequestGlue->sendResponse();
            }
        }

        public static function dispatch()
        {
            self::doBaseStuff();
            exit(0);
        }

        public static function redirect(String $sController, String $sAction)
        {
            self::$sController = $sController;
            self::$sAction = $sAction;
            self::doBaseStuff();
            exit(0);
        }

        public static function setWebRequestGlue(WebRequestGlueInterface $webRequestGlue)
        {
            self::$webRequestGlue = $webRequestGlue;
        }
    }
}
