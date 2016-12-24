<?php
use Diana\Core\Std\StringType;

function passwordField(
                       StringType $sName = null,
                       StringType $sId = null,
                       StringType $sVal = null,
                       $iSize = null,
                       $iMaxLength = null,
                       $bDisabled = false
                       )
{
    $str = '<input type="password"';
    if ($sName != null) {
        $str .= ' name="' .  $sName . '"';
    }
    if ($sVal != null) {
        $str .= ' value="' . $sVal . '"';
    }
    if (is_int($iSize)) {
        $str .= ' size="' . $iSize . '"';
    }
    if (is_int($iMaxLength)) {
        $str .= ' maxlength="' . $iMaxLength . '"';
    }
    if ($bDisabled) {
        $str .= ' disabled="disabled"';
    }
    $str .= ' />';

    echo $str . PHP_EOL;
}
