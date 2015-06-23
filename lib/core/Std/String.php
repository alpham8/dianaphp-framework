<?php
/**
 * String.php
 *
 * This is my implementation of String class for PHP. It has all common features,
 * which are well-known from other OOP languages (like C# or Java)
 *
 * @package De.Twunner.Std
 * @version v0.0.1
 * @author Thomas Wunner <th.wunner@gmx.de>
 * @copyright CC by SA Copyrith (c) 2014, Thomas Wunner
 *
 *
 * @since API v0.0.1
 */
require_once_file(EPHP_CORE . 'Util/ExceptionView.php');


class String
{
    private $_strCurrent;
    public $length;

    private static function _typeError ($strMsg)
    {
        throw new Exception('Type Error! ' . $strMsg);
    }

    public function __construct($strCurrent = '')
    {
        // TODO: Encoding Unterscheidung noch einbauen!!!
        if (!is_string($strCurrent))
        {
            self::_typeError('Class String must be initiliazed with a String. Trace: ' . ExceptionView::prettyPrintTrace());
        }
        $this->_strCurrent = $strCurrent;
        $this->length = strlen($this->_strCurrent);
    }

    public function startsWith($strPartToCheck)
    {
        if (!is_string($strPartToCheck) && $strPartToCheck instanceof String)
        {
            $strPartToCheck = $strPartToCheck->__toString();
        }

        elseif (is_string($strPartToCheck))
        {

        }

        else
        {
            self::_typeError('Part String must be a String itself.');
        }

        if ($this->length < strlen($strPartToCheck))
        {
            return false;
        }

        return substr($this->_strCurrent, 0, strlen($strPartToCheck)) === $strPartToCheck;
    }

    public function endsWith($strPartToCheck)
    {
        if (!is_string($strPartToCheck) && $strPartToCheck instanceof String)
        {
            $strPartToCheck = $strPartToCheck->__toString();
        }

        elseif (is_string($strPartToCheck))
        {

        }

        else
        {
            self::_typeError('Part String must be a String itself.');
        }

        if ($this->length < strlen($strPartToCheck))
        {
            return false;
        }

        return substr($this->_strCurrent, $this->length - strlen($strPartToCheck), strlen($strPartToCheck)) === $strPartToCheck;
    }

    public function matches($strRegexPattern, &$arMatches = null)
    {
        if ($arMatches !== null)
        {
            return preg_match_all($strRegexPattern, $this->_strCurrent, $arMatches) >= 1 ? true : false;
        }
        return preg_match($strRegexPattern, $this->_strCurrent) === 1 ? true : false;
    }

    public function substring($iStart, $iEnd = 0)
    {
        if ($iEnd > 0 && $iStart < $iEnd)
        {
            $iSubstrLen = $iEnd - $iStart;
            return new String(substr($this->_strCurrent, $iStart, $iSubstrLen));
        }
        else
        {
            return new String(substr($this->_strCurrent, $iStart));
        }
    }

    public function trim()
    {
        return new String(trim($this->_strCurrent));
    }

    public function lTrim()
    {
        return new String(ltrim($this->_strCurrent));
    }

    public function rTrim()
    {
        return new String(rtrim($this->_strCurrent));
    }

    public function ucFirst()
    {
        return new String(ucfirst($this->_strCurrent));
    }

    public function lcFirst()
    {
        return new String(lcfirst($this->_strCurrent));
    }

    public function toLower()
    {
        return new String(strtolower($this->_strCurrent));
    }

    public function toUpper()
    {
        return new String(strtoupper($this->_strCurrent));
    }

    public function isEmpty()
    {
        return empty($this->_strCurrent);
    }

    public function __toString()
    {
        return $this->_strCurrent;
    }

    public function getEnumerator()
    {
        return str_split($this->_strCurrent);
    }

    public function indexOf($sSearchTo)
    {
        if ($sSearchTo instanceof String)
        {
            $sSearchTo = $sSearchTo->__toString();
        }

        if (empty($sSearchTo))
        {
            return -1;
        }

        return strpos($this->_strCurrent, $sSearchTo);
    }

    public function lastIndexOf($sSearchTo)
    {
        if ($sSearchTo instanceof String)
        {
            $sSearchTo = $sSearchTo->__toString();
        }

        if (empty($sSearchTo))
        {
            return -1;
        }

        return strrpos($this->_strCurrent, $sSearchTo);
    }

    public function arrayToString($charArray, $strGlue = '')
    {
        return implode($strGlue, $charArray);
    }

    public function splitBy($str, $iLimit = -1)
    {
        if ($iLimit > -1)
        {
            return explode($str, $this->_strCurrent, $iLimit);
        }

        return explode($str, $this->_strCurrent);
    }

    public function splitToStringsBy($str, $iLimit = -1)
    {
        $arRet = explode($str, $this->_strCurrent);
        for ($i = 0; $i < count($arRet); $i++)
        {
            $arRet[$i] = new String($arRet[$i]);
        }
        return $arRet;
    }

    public function replace($strSearch, $strReplace)
    {
        $strSearch = $strSearch instanceof String ? $strSearch->__toString() : $strSearch;
        $strReplace = $strReplace instanceof String ? $strReplace->__toString() : $strReplace;

        return new String(str_replace($strSearch, $strReplace, $this->_strCurrent));
    }

    public function regReplace($strPattern, $strReplace)
    {
        return preg_replace($strPattern, $strReplace, $this->_strCurrent);
    }

    public function padRigtht($iSpaces, $bHtml = false)
    {
        $strBase = '';
        if ($bHtml)
        {
            $strBase = '<span style="text-align: right;">';
            for ($i = 1; $i <= $iSpaces; $i++)
            {
                $strBase .= '&nbsp;';
            }
            $strBase .= $this->_strCurrent . '</span>';
        }
        else
        {
            for ($j = 1; $j <= $iSpaces; $i++)
            {
                $strBase .= ' ';
            }
            $strBase .= $this->_strCurrent;
        }
        return new String($strBase);
    }

    public function padLeft($iSpaces, $bHtml = false)
    {
        $strBase = '';
        if ($bHtml)
        {
            $strBase = '<span style="text-align: left;">';
            for ($i = 1; $i <= $iSpaces; $i++)
            {
                $strBase .= '&nbsp;';
            }
            $strBase .= $this->_strCurrent;
        }
        else
        {
            for ($j = 1; $j <= $iSpaces; $j++)
            {
                $strBase .= ' ';
            }
            $strBase .= $this->_strCurrent;
        }

        return new String($strBase);
    }

    public function contains($strNeedle)
    {
        $strNeedle = $strNeedle instanceof String ? $strNeedle->__toString() : $strNeedle;

        return strpos($this->_strCurrent, $strNeedle) != false  ? true : false;
    }

    public function compareTo($strToCompare)
    {
        $strToCompare = $strToCompare instanceof String ? $strToCompare->__toString() : $strToCompare;

        return strcmp($this->_strCurrent, $strToCompare);
    }

    public function equals($sEqualTo)
    {
        if ($sEqualTo instanceof String)
        {
            $sEqualTo = $sEqualTo->__toString();
        }

        return $this->_strCurrent === $sEqualTo;
    }
}
?>
