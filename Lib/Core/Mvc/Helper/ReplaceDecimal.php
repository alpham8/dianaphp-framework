<?php
function ReplaceDecimal($dValue)
{
    // is needed to parse the value to a string
    $sDecimal = new String($dValue . '');

    return $sDecimal->replace('.', ',')->__toString();
}
