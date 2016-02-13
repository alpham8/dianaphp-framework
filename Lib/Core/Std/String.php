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
namespace Diana\Core\Std
{
	use Diana\Core\Util\ExceptionView;

	class String
	{
		private $_strCurrent;
		private $_bMb;
		public $length;

		private static function _typeError ($strMsg)
		{
			throw new \Exception('Type Error! ' . $strMsg);
		}

		public function __construct($strCurrent = '')
		{
			if (!is_string($strCurrent))
			{
				self::_typeError('Class String must be initiliazed with a String. Trace: '
                                    . ExceptionView::prettyPrintTrace());
			}

			$this->_strCurrent = $strCurrent;

			if (function_exists('mb_detect_encoding') && mb_detect_encoding($this->_strCurrent) !== 'ascii')
			{
				$this->_bMb = true;
				$this->length = mb_strlen($this->_strCurrent);
			}

			else
			{
				$this->_bMb = false;
				$this->length = strlen($this->_strCurrent);
			}
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

			$strEncoding = $this->_bMb ? mb_detect_encoding($strPartToCheck) : 'ascii';

			if ($strEncoding === 'ascii' && $this->length < strlen($strPartToCheck))
			{
				return false;
			}

			elseif ($strEncoding !== 'ascii' && $this->length < mb_strlen($strPartToCheck))
			{
				return false;
			}

			//substr($this->_strCurrent, 0, strlen($strPartToCheck)) === $strPartToCheck;

			$bRet = false;
			if ($this->_bMb && $strEncoding !== 'ascii')
			{
				$bRet = mb_substr($this->_strCurrent, 0, mb_strlen($strPartToCheck)) === $strPartToCheck;
			}

			elseif ($this->_bMb && $strEncoding === 'ascii')
			{
				$bRet = mb_substr($this->_strCurrent, 0, strlen($strPartToCheck)) == $strPartToCheck;
			}

			elseif (!$this->_bMb && $strEncoding === 'ascii')
			{
				$bRet = substr($this->_strCurrent, 0, strlen($strPartToCheck)) === $strPartToCheck;
			}

			elseif (!$this->_bMb && $strEncoding !== 'ascii')
			{
				$strComparsion = mb_convert_encoding($this->_strCurrent, $strEncoding, 'ascii');
				$bRet = mb_substr($strComparsion, 0, mb_strlen($strPartToCheck)) === $strPartToCheck;
			}

			return $bRet;
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

			$strEncoding = $this->_bMb ? mb_detect_encoding($strPartToCheck) : 'ascii';

			if ($strEncoding === 'ascii' && $this->length < strlen($strPartToCheck))
			{
				return false;
			}

			elseif ($strEncoding !== 'ascii' && $this->length < mb_strlen($strPartToCheck))
			{
				return false;
			}

			$bRet = false;
			if ($this->_bMb && $strEncoding !== 'ascii')
			{
				$bRet = mb_substr($this->_strCurrent, $this->length -  mb_strlen($strPartToCheck)) === $strPartToCheck;
			}

			elseif ($this->_bMb && $strEncoding === 'ascii')
			{
				$bRet = mb_substr($this->_strCurrent, $this->length - strlen($strPartToCheck)) == $strPartToCheck;
			}

			elseif (!$this->_bMb && $strEncoding === 'ascii')
			{
				$bRet = substr($this->_strCurrent, $this->length - strlen($strPartToCheck)) === $strPartToCheck;
			}

			elseif (!$this->_bMb && $strEncoding !== 'ascii')
			{
				$strComparsion = mb_convert_encoding($this->_strCurrent, $strEncoding, 'ascii');
				$bRet = mb_substr($strComparsion, $this->length - mb_strlen($strPartToCheck)) === $strPartToCheck;
			}

			return $bRet;
		}

		public function matches($strRegexPattern, &$arMatches = null)
		{
			if ($this->_bMb)
			{
				$strRegexPattern = $this->_sanitizeMbRegex($strRegexPattern);

				return $arMatches === null ?
					mb_ereg($strRegexPattern, $this->_strCurrent)
						: mb_ereg($strRegexPattern, $this->_strCurrent, $arMatches);
			}

			else
			{
				return $arMatches === null ?
					preg_match($strRegexPattern, $this->_strCurrent)
						: preg_match_all($strRegexPattern, $this->_strCurrent, $arMatches);
			}
		}

		public function substring($iStart, $iEnd = 0)
		{
			if ($iEnd > 0 && $iStart < $iEnd)
			{
				$iSubstrLen = $iEnd - $iStart;

				return $this->_bMb ? new String(mb_substr($this->_strCurrent, $iStart, $iSubstrLen)) : new String(substr($this->_strCurrent, $iStart, $iSubstrLen));
			}

			else
			{
				return $this->_bMb ? new String(mb_substr($this->_strCurrent, $iStart)) : new String(substr($this->_strCurrent, $iStart));
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
			// 2nd check to return false in case of '0'
			return empty($this->_strCurrent) && $this->length < 1;
		}

		public function __toString()
		{
			return $this->_strCurrent;
		}

		// TODO: Method below
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

			return $this->_bMb ?
				mb_strpos($this->_strCurrent,
							mb_convert_encoding($sSearchTo, mb_detect_encoding($this->_strCurrent)))
				: strpos($this->_strCurrent, $sSearchTo);
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

			return $this->_bMb ?
				mb_strrpos($this->_strCurrent,
							mb_convert_encoding($sSearchTo, mb_detect_encoding($this->_strCurrent)))
				: strrpos($this->_strCurrent, $sSearchTo);
		}

		public function arrayToString($charArray, $strGlue = '')
		{
			return new String(implode($strGlue, $charArray));
		}

		public function splitBy($str, $iLimit = -1)
		{
			$str instanceof String ? $str->__toString() : $str;

			if ($iLimit > -1)
			{
				return $this->_bMb ? mb_split($this->_sanitizeMbRegex($str), $this->_strCurrent, $iLimit) : explode($str, $this->_strCurrent, $iLimit);
			}

			else
			{
				return $this->_bMb ? mb_split($this->_sanitizeMbRegex($str), $this->_strCurrent) : explode($str, $this->_strCurrent);
			}
		}

		public function splitToStringsBy($str, $iLimit = -1)
		{
			$str instanceof String ? $str->__toString() : $str;

			$arRet = $this->splitBy($str, $iLimit);
			// TODO: maybe foreach for speed improvements?
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
			$strReplace instanceof String ? $strReplace->__toString() : $strReplace;

			if ($this->_bMb)
			{
				$sEncoding = mb_detect_encoding($this->_strCurrent);
				$strPattern = $this->_sanitizeMbRegex($strPattern);

				return new String(mb_ereg_replace($strPattern,
													mb_convert_encoding($strReplace, $sEncoding),
													$this->_strCurrent));
			}

			else
			{
				return new String(preg_replace($strPattern, $strReplace, $this->_strCurrent));
			}
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

			if ($this->_bMb)
			{
				$sEncoding = mb_detect_encoding($this->_strCurrent);

				return mb_strpos($this->_strCurrent, mb_convert_encoding($strNeedle, $sEncoding)) != false;
			}

			else
			{
				return strpos($this->_strCurrent, $strNeedle) != false;
			}
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

		protected function _sanitizeMbRegex($strRegexPattern)
		{
			$strRegexPattern instanceof String ? $strRegexPattern->__toString() : $strRegexPattern;
			$sEncoding = mb_detect_encoding($this->_strCurrent);
			mb_regex_encoding($sEncoding);
			$strRegexPattern = mb_convert_encoding($strRegexPattern, $sEncoding);

			return mb_strpos($strRegexPattern, '/') === 0
								&& mb_strrpos($strRegexPattern, '/') === (mb_strlen($strRegexPattern) - 1)
									? mb_substr($strRegexPattern, 1, mb_strlen($strRegexPattern) - 2)
										: $strRegexPattern;
		}
	}
}
?>
