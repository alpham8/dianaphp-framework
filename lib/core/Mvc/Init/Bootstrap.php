<?php
function require_file($sFile)
{
    require($sFile);
}

function require_once_file($sFile)
{
    require_once($sFile);
}

function include_once_file($sFile)
{
	include_once($sFile);
}

function include_file($sFile)
{
	include($sFile);
}

include(EPHP_CORE . 'autoload.php');
include(EPHP_CORE . 'Mvc/Init/Dispatcher.php');

// Localize Langauge for Date settings
// FF on Linux: de-de,de;q=0.8,en-us;q=0.5,en;q=0.3

class Bootstrap
{
    public function init()
    {
		$sRequestUri = new String(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_REQUEST_URI']);
		$sRequestUri = new String('http://' . $_SERVER['HTTP_HOST'] . $sRequestUri->__toString());
		Dispatcher::init($sRequestUri);
		Dispatcher::dispatch();
    }
}

?>
