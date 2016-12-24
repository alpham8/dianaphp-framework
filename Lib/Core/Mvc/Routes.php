<?php
namespace Diana\Core\Mvc
{
    use Diana\Core\Std\StringType;
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
                                        StringType $sRedirectController,
                                        StringType $sRedirectAction, $bSoft = true
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
        public static function getRoute(StringType $sRoute)
        {
            $sFound = new StringType();

            $sSimple = $sRoute->__toString();

            foreach (self::$arRoutes as $sRouteKey => $sRouteVal) {
                if ($sRouteKey === $sSimple) {
                    return $sRouteVal;
                }
            }

            return $sRoute;
        }

        public function addRoute(StringType $sAlias, StringType $sRoute)
        {
            self::$arRoutes[$sAlias->__toString()] = $sRoute;
        }
    }
}
