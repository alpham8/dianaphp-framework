<?php
namespace Diana\Core\Util
{
    class ExceptionView
    {
        public static function prettyPrintTrace($isHtml = true)
        {
            $arTrace = debug_backtrace();

            $sRet = '';
            foreach ($arTrace as $arSingle) {
                if (
                    array_key_exists('file', $arSingle)
                    && array_key_exists('line', $arSingle)
                    && array_key_exists('function', $arSingle)
                ) {
                    $sRet = $sRet
                            . ' File : '
                            . $arSingle['file']
                            . ' on Line: '
                            . $arSingle['line']
                            . ' in Function: '
                            . $arSingle['function']
                            . ($isHtml ? '<br />' : PHP_EOL);
                } elseif (
                    array_key_exists('file', $arSingle)
                    && array_key_exists('line', $arSingle)
                ) {
                    $sRet = $sRet
                            . ' File : '
                            . $arSingle['file']
                            . ' on Line: '
                            . $arSingle['line']
                            . ($isHtml ? '<br />' : PHP_EOL);
                } elseif (array_key_exists('file', $arSingle)) {
                    $sRet = $sRet . ' File: ' . $arSingle['file'] . ($isHtml ? '<br />' : PHP_EOL);
                } else {
                    $sRet = $sRet . 'unknown' . ($isHtml ? '<br />' : PHP_EOL);
                }
            }

            if (
                !isset($sRet)
                || $sRet === null
            ) {
                return null;
            }

            return $sRet;
        }
    }
}
