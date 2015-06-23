<?php
function textfield(String $sName = null, String $sId = null, String $sVal = null, $bReadonly = null, $iSize = null, $iMaxLength = null)
{
	$str = '<input type="text"';

	if ($sName != null)
	{
		$str .= ' name="' .  $sName . '"';
	}

	if ($sId != null)
	{
		$str .= ' id="' . $sId . '"';
	}

	if ($sVal != null)
	{
		$str .= ' value="' . $sVal . '"';
	}

	if (is_bool($bReadonly) && $bReadonly === true)
	{
		$str .= ' readonly';
	}

	if (is_int($iSize))
	{
		$str .= ' size="' . $iSize . '"';
	}

	if (is_int($iMaxLength))
	{
		$str .= ' maxlength="' . $iMaxLength . '"';
	}

	$str .= ' />';

	echo $str . PHP_EOL;
}
?>
