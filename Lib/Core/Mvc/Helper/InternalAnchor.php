<?php
use Diana\Core\Mvc\View;
use Diana\Core\Std\String;

function internalAnchor(View $view, String $sController, String $sAction, $arParams = null)
{
	$request = $view->getRequest();
	$sParams = '';

	if ($arParams != null && is_array($arParams))
	{
		foreach ($arParams as $sParamName => $sParamValue)
		{
			$sParams = new String($sParams . urlencode($sParamName . '=' . $sParamValue) . '/');
		}
		$sParams = $sParams->endsWith('/') ? $sParams->substring(0, $sParams->length - 1) : $sParams;
	}

	return $request->getBaseUri() . $sController->__toString() . '/' . $sAction->__toString() . '/' . $sParams;
}
?>
