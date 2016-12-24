<?php
namespace Diana\Core\Util\Authentification
{
    use Diana\Core\Std\StringType;

    class Session
    {
        protected static $instance;

        private function __construct()
        {
            $this->start();
        }

        public function start()
        {
            return session_start();
        }

        public function set(StringType $sKey, StringType $sValue)
        {
            $_SESSION[$sKey->__toString()] = $sValue;
        }

        public function get(StringType $sKey)
        {
            $sKeySimple = $sKey->__toString();
            if (isset($_SESSION[$sKeySimple])) {
                return $_SESSION[$sKeySimple];
            }
            return false;
        }

        public function remove(StringType $sKey)
        {
            $sKeySimple = $sKey->__toString();
            unset($_SESSION[$sKeySimple]);
        }

        public function destroy()
        {
            self::$instance = null;
            session_destroy();
        }

        public static function getInstance()
        {
            if (empty(self::$instance) && !self::$instance instanceof Session) {
                self::$instance = new Session();
            }
            return self::$instance;
        }
    }
}
