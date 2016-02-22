<?php
require('Lib' . DIRECTORY_SEPARATOR . 'config.php');
require(ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

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

function checkstring($str)
{
	return $str !== null && $str instanceof Diana\Core\Std\String && !$str->isEmpty();
}

function array_filled(&$ar)
{
	return isset($ar) && is_array($ar) && count($ar) > 0;
}

$bs = new Diana\Core\Mvc\Init\Bootstrap();
$bs->init();
?>
