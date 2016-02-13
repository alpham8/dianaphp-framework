<?php
function __autoload($sClass)
{
	if ($sClass === 'String')
	{
		require_once_file(DIANA_CORE . 'Std/String.php');
	}
	elseif ($sClass === 'Date')
	{
		require_once_file(DIANA_CORE . 'Std/Date.php');
	}
	elseif ($sClass === 'Tokenizer')
	{
		require_once_file(DIANA_CORE . 'Std/Tokenizer.php');
	}
	elseif ($sClass === 'CultureInfo')
	{
		require_once_file(DIANA_CORE . 'Util/CultureInfo/CultureInfo2.php');
	}
	elseif ($sClass === 'Routes')
	{
		require_once_file(DIANA_CORE . 'Mvc/Routes.php');
	}
}

function checkstring($str)
{
	return $str !== null && $str instanceof String && !$str->isEmpty();
}

function array_filled(&$ar)
{
	return isset($ar) && is_array($ar) && count($ar) > 0;
}
?>
