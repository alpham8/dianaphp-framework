<?php
class CultureInfo
{
	public static function setTo($sLocale)
	{
		Locale::setDefault($sLocale);
		setlocale(LC_TIME, $sLocale);
        setlocale(LC_ALL, $sLocale);
	}
}
?>