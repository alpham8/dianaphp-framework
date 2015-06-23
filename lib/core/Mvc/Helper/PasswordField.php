<?php
function passwordField(String $sName = null, String $sId = null, String $sVal = null, $iSize = null, $iMaxLength = null, $bDisabled = false)
{
	$str = '<input type="password"';
	if ($sName != null)
	{
		$str .= ' name="' .  $sName . '"';
	}
	if ($sVal != null)
	{
		$str .= ' value="' . $sVal . '"';
	}
	if (is_int($iSize))
	{
		$str .= ' size="' . $iSize . '"';
	}
	if (is_int($iMaxLength))
	{
		$str .= ' maxlength="' . $iMaxLength . '"';
	}
	if ($bDisabled)
	{
		$str .= ' disabled="disabled"';
	}
	$str .= ' />';

	echo $str . PHP_EOL;
}
?>
