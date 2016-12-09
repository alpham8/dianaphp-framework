<?php
namespace Diana\Core\Mvc
{
    use Diana\Core\Std\String;
    use Diana\Core\Mvc\Init\Dispatcher;
    use Diana\Core\Std\Http\Request;
    use Diana\Core\Std\Http\Response;

    abstract class Routes
    {
        private static $arRoutes = array();
        private static $sCurrent;

        public static function readIni()
        {
            // TODO
        }

        public static function redirect(
                                        String $sRedirectController,
                                        String $sRedirectAction, $bSoft = true
                                        )
        {
            if ($bSoft) {
                Dispatcher::redirect($sRedirectController, $sRedirectAction);
            } else {
                $request = new Request();
                header('Location: '
                       . $request->getBaseUri()
                       . $sRedirectController->__toString() . '/'
                       . $sRedirectAction->__toString());
                exit(0);
            }
        }

        /**
         * get the original route to an given alias
         * if this route wasnot added before, it returns $sRoute
         *
         * @return String $alias
         */
        public static function getRoute(String $sRoute)
        {
            $sFound = new String();

            $sSimple = $sRoute->__toString();

            foreach (self::$arRoutes as $sRouteKey => $sRouteVal) {
                if ($sRouteKey === $sSimple) {
                    return $sRouteVal;
                }
            }

            return $sRoute;
        }

        public function addRoute(String $sAlias, String $sRoute)
        {
            self::$arRoutes[$sAlias->__toString()] = $sRoute;
        }
    }
}
