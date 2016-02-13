<?php
use Diana\Core\Std\String;
use App\Mvc\Model\TranslationModel;

function translate(String $sKey)
{
	$transMdl = new TranslationModel();
	$transMdl->setKey($sKey);
	$transMdl->fetch();

	$sLocale = new String(\Locale::getDefault());
	$sLocale = $sLocale->substring(0, 2)->toLower()->ucFirst()->__toString();
	$sMethod = 'get' . $sLocale;
	$sChoosenLang = $transMdl->$sMethod();

	if (checkstring($sChoosenLang))
	{
		return $sChoosenLang;
	}

	return $transMdl->getEn();
}
?>
