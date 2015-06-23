<?php
class Session
{
    protected static $_instance;

    private function __construct()
    {
		$this->start();
    }

    public function start()
    {
		return session_start();
    }

    public function set(String $sKey, String $sValue)
    {
		$_SESSION[$sKey->__toString()] = $sValue;
    }

    public function get(String $sKey)
    {
		$sKeySimple = $sKey->__toString();
		if (isset($_SESSION[$sKeySimple]))
			return $_SESSION[$sKeySimple];
		return false;
    }

	public function remove(String $sKey)
	{
		$sKeySimple = $sKey->__toString();
		unset($_SESSION[$sKeySimple]);
	}

    public function destroy()
    {
		self::$_instance = null;
		session_destroy();
    }

    public static function getInstance()
    {
		if (empty(self::$_instance) && !self::$_instance instanceof Session)
		{
			self::$_instance = new Session();
		}
		return self::$_instance;
    }
}
?>
