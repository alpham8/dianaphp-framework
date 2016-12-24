<?php
/**
 * StringType.php
 *
 * This is my implementation of String class for PHP. It has all common features,
 * which are well-known from other OOP languages (like C# or Java)
 *
 * @package Diana.Core.Std
 * @version v0.0.1
 * @author Thomas Wunner <info@wunner-software.de>
 *
 *
 * @since API v0.0.1
 */
namespace Diana\Core\Std
{
    use Diana\Core\Util\ExceptionView;

    // TODO: mb_http_output einbauen fuer JSON response
    class StringType
    {
        private $strCurrent;
        private $bMb;
        public $length;

        private static function typeError($strMsg)
        {
            throw new \Exception('Type Error! ' . $strMsg);
        }

        public function __construct($strCurrent = '')
        {
            if ($strCurrent instanceof StringType) {
                $strCurrent = $strCurrent->__toString();
            } elseif (!is_string($strCurrent)) {
                self::typeError(
                    'Class String must be initiliazed with a String. Trace: '
                    . ExceptionView::prettyPrintTrace()
                );
            }

            $this->strCurrent = $strCurrent;

            if (
                function_exists('mb_detect_encoding')
                && mb_detect_encoding($this->strCurrent) !== 'ascii'
            ) {
                $this->bMb = true;
                $this->length = mb_strlen($this->strCurrent);
            } else {
                $this->bMb = false;
                $this->length = strlen($this->strCurrent);
            }
        }

        public function startsWith($strPartToCheck)
        {
            if (!is_string($strPartToCheck)
                && $strPartToCheck instanceof StringType
            ) {
                $strPartToCheck = $strPartToCheck->__toString();
            } elseif (!is_string($strPartToCheck)) {
                self::typeError('Part String must be a String itself.');
            }

            $strEncoding = $this->bMb
                            ? mb_strtolower(mb_detect_encoding($strPartToCheck))
                            : 'ascii';

            if (
                $strEncoding === 'ascii'
                && $this->length < strlen($strPartToCheck)
            ) {
                return false;
            } elseif (
                $strEncoding !== 'ascii'
                && $this->length < mb_strlen($strPartToCheck)
            ) {
                return false;
            }

            $bRet = false;

            if ($this->bMb && $strEncoding !== 'ascii') {
                $bRet = mb_substr(
                            $this->strCurrent,
                            0,
                            mb_strlen($strPartToCheck)
                        ) === $strPartToCheck;
            } elseif (
                $this->bMb
                && $strEncoding === 'ascii'
            ) {
                $bRet = mb_substr(
                            $this->strCurrent,
                            0,
                            strlen($strPartToCheck)
                        ) === $strPartToCheck;
            } elseif (
                !$this->bMb
                && $strEncoding === 'ascii'
            ) {
                $bRet = substr(
                            $this->strCurrent,
                            0,
                            strlen($strPartToCheck)
                        ) === $strPartToCheck;
            } elseif (
                !$this->bMb
                && $strEncoding !== 'ascii'
            ) {
                $strComparsion = mb_convert_encoding($this->strCurrent, $strEncoding, 'ascii');
                $bRet = mb_substr(
                            $strComparsion,
                            0,
                            mb_strlen($strPartToCheck)
                        ) === $strPartToCheck;
            }

            return $bRet;
        }

        public function endsWith($strPartToCheck)
        {
            if (
                !is_string($strPartToCheck)
                && $strPartToCheck instanceof StringType
            ) {
                $strPartToCheck = $strPartToCheck->__toString();
            } elseif (!is_string($strPartToCheck)) {
                self::typeError('Part String must be a String itself.');
            }

            $strEncoding = $this->bMb
                            ? mb_strtolower(mb_detect_encoding($strPartToCheck))
                            : 'ascii';

            if (
                $strEncoding === 'ascii'
                && $this->length < strlen($strPartToCheck)
            ) {
                return false;
            } elseif (
                $strEncoding !== 'ascii'
                && $this->length < mb_strlen($strPartToCheck)
            ) {
                return false;
            }

            $bRet = false;

            if (
                $this->bMb
                && $strEncoding !== 'ascii'
            ) {
                $bRet = mb_substr(
                            $this->strCurrent,
                            $this->length -  mb_strlen($strPartToCheck)
                        ) === $strPartToCheck;
            } elseif (
                $this->bMb
                && $strEncoding === 'ascii'
            ) {
                $bRet = mb_substr(
                            $this->strCurrent,
                            $this->length - strlen($strPartToCheck)
                        ) == $strPartToCheck;
            } elseif (
                !$this->bMb
                && $strEncoding === 'ascii'
            ) {
                $bRet = substr(
                            $this->strCurrent,
                            $this->length - strlen($strPartToCheck)
                        ) === $strPartToCheck;
            } elseif (
                !$this->bMb
                && $strEncoding !== 'ascii'
            ) {
                $strComparsion = mb_convert_encoding(
                                    $this->strCurrent,
                                    $strEncoding, 'ascii'
                                );
                $bRet = mb_substr(
                            $strComparsion,
                            $this->length - mb_strlen($strPartToCheck)
                        ) === $strPartToCheck;
            }

            return $bRet;
        }

        /**
         * matches a string by the given regular expression pattern
         *
         * @param string $strRegexPattern the pattern to search for
         * @param array $arMatches       the found results, if given
         *
         * @return boolean    true if matched, otherwise false
         */
        public function matches($strRegexPattern, &$arMatches = null)
        {
            if ($this->bMb) {
                $strRegexPattern = $strRegexPattern instanceof StringType
                                        ? $strRegexPattern
                                        : new StringType($strRegexPattern);

                if ($strRegexPattern->startsWith('/')) {
                    $iLastSlash = $strRegexPattern->lastIndexOf('/') + 1;
                    $modifiers = $strRegexPattern->substring($iLastSlash);
                    $modifiers = $modifiers->contains('u')
                                    ? $modifiers
                                    : new StringType($modifiers . 'u');
                    $strRegexPattern = new StringType(
                                        $strRegexPattern
                                            ->substring(
                                                0,
                                                $iLastSlash
                                            )
                                        . $modifiers
                                        );
                } else {
                    $strRegexPattern = new StringType('/' . $strRegexPattern . '/u');
                }

                if ($arMatches === null) {
                    return preg_match($strRegexPattern->__toString(), $this->strCurrent) === 1;
                } else {
                    // safe unicode regex check
                    // could be also done with the u modifier in preg_match_all
                    // @see http://stackoverflow.com/questions/1766485/are-the-php-preg-functions-multibyte-safe/1766546#1766546
                    // @see http://stackoverflow.com/questions/7675627/multi-byte-function-to-replace-preg-match-all
                    //mb_ereg_search_init($this->strCurrent, $strRegexPattern);
                    //$arMatches = mb_ereg_search_regs($strRegexPattern);
                    //
                    //return is_array($arMatches) && count($arMatches) > 0;
                    // TODO: Fix here maybe with mb_split to get all the matches back and then divide into groups

                    return preg_match_all($strRegexPattern, $this->strCurrent, $arMatches);
                }
            } else {
                return $arMatches === null
                    ? preg_match(
                        $strRegexPattern,
                        $this->strCurrent
                    ) === 1
                    : preg_match_all(
                        $strRegexPattern,
                        $this->strCurrent,
                        $arMatches
                    );
            }
        }

        /**
         * @param int $iStart
         * @param int $iEnd optional end
         * get the string part at the defined start and end.
         * If end is not given, it will give back the whole string back at the start offset
         * @return String a new String instance
         */
        public function substring($iStart, $iEnd = 0)
        {
            if (
                $iEnd > 0
                && $iStart < $iEnd
            ) {
                $iSubstrLen = $iEnd - $iStart;

                return $this->bMb
                            ? new StringType(
                                mb_substr(
                                    $this->strCurrent,
                                    $iStart,
                                    $iSubstrLen
                                )
                            ) : new StringType(
                                substr(
                                    $this->strCurrent,
                                    $iStart,
                                    $iSubstrLen
                                )
                            );
            } else {
                return $this->bMb
                    ? new StringType(
                        mb_substr(
                            $this->strCurrent,
                            $iStart
                        )
                    ) : new StringType(
                            substr(
                                $this->strCurrent,
                                $iStart
                            )
                    );
            }
        }

        /**
         * remove all whitespaces at the beginning and at the end
         *
         * @return String    A new String instance
         */
        public function trim()
        {
            return new StringType(trim($this->strCurrent));
        }

        public function lTrim()
        {
            return new StringType(ltrim($this->strCurrent));
        }

        public function rTrim()
        {
            return new StringType(rtrim($this->strCurrent));
        }

        public function ucFirst()
        {
            return new StringType(ucfirst($this->strCurrent));
        }

        public function lcFirst()
        {
            if ($this->bMb) {
                return new StringType(
                    mb_strtoupper(
                        mb_substr(
                            $this->strCurrent,
                            0,
                            1
                        )
                    )
                    . mb_substr(
                        $this->strCurrent,
                        1
                    )
                );
            } else {
                return new StringType(lcfirst($this->strCurrent));
            }
        }

        public function toLower()
        {
            return new StringType(
                $this->bMb
                    ? mb_strtolower($this->strCurrent)
                    : strtolower($this->strCurrent)
            );
        }

        public function toUpper()
        {
            return new StringType(
                $this->bMb
                    ? mb_strtoupper($this->strCurrent)
                    : strtoupper($this->strCurrent)
            );
        }

        public function isEmpty()
        {
            // 2nd check to return false in case of '0'
            return empty($this->strCurrent) && $this->length < 1;
        }

        public function __toString()
        {
            return $this->strCurrent;
        }

        public function getEnumerator()
        {
            if ($this->bMb) {
                $arChars = array();
                for ($i = 0; $i < $this->length; $i++) {
                    $arChars[] = mb_substr($this->strCurrent, $i, 1);
                }

                return $arChars;
            } else {
                return str_split($this->strCurrent);
            }
        }

        public function indexOf($sSearchTo)
        {
            if ($sSearchTo instanceof StringType) {
                $sSearchTo = $sSearchTo->__toString();
            }

            if (empty($sSearchTo)) {
                return -1;
            }

            return $this->bMb ?
                mb_strpos(
                    $this->strCurrent,
                    mb_convert_encoding(
                        $sSearchTo,
                        mb_detect_encoding($this->strCurrent)
                    )
                )
                : strpos(
                    $this->strCurrent,
                    $sSearchTo
                );
        }

        public function lastIndexOf($sSearchTo)
        {
            if ($sSearchTo instanceof StringType) {
                $sSearchTo = $sSearchTo->__toString();
            }

            if (empty($sSearchTo)) {
                return -1;
            }

            return $this->bMb
                ? mb_strrpos(
                    $this->strCurrent,
                    mb_convert_encoding(
                        $sSearchTo,
                        mb_detect_encoding($this->strCurrent)
                    )
                )
                : strrpos(
                    $this->strCurrent,
                    $sSearchTo
                );
        }

        public function arrayToString($charArray, $strGlue = '')
        {
            return new StringType(implode($strGlue, $charArray));
        }

        public function splitBy($str, $iLimit = -1)
        {
            $str instanceof StringType ? $str->__toString() : $str;

            if ($iLimit > -1) {
                return $this->bMb
                    ? mb_split(
                        $this->sanitizeMbRegex($str),
                        $this->strCurrent,
                        $iLimit
                    )
                    : explode(
                        $str,
                        $this->strCurrent,
                        $iLimit
                    );
            } else {
                return $this->bMb
                    ? mb_split(
                        $this->sanitizeMbRegex($str),
                        $this->strCurrent
                    )
                    : explode(
                        $str,
                        $this->strCurrent
                    );
            }
        }

        public function splitToStringsBy($str, $iLimit = -1)
        {
            $str instanceof StringType ? $str->__toString() : $str;

            $arRet = $this->splitBy($str, $iLimit);
            foreach ($arRet as $iIndex => $strCurrent) {
                $arRet[$iIndex] = new StringType($strCurrent);
            }

            return $arRet;
        }

        public function replace($strSearch, $strReplace)
        {
            $strSearch = $strSearch instanceof StringType ? $strSearch->__toString() : $strSearch;
            $strReplace = $strReplace instanceof StringType ? $strReplace->__toString() : $strReplace;

            return new StringType(str_replace($strSearch, $strReplace, $this->strCurrent));
        }

        public function regReplace($strPattern, $strReplace)
        {
            $strReplace instanceof StringType ? $strReplace->__toString() : $strReplace;

            if ($this->bMb) {
                $sEncoding = mb_detect_encoding($this->strCurrent);
                $strPattern = $this->sanitizeMbRegex($strPattern);

                return new StringType(
                    mb_ereg_replace(
                        $strPattern,
                        mb_convert_encoding($strReplace, $sEncoding),
                        $this->strCurrent)
                );
            } else {
                return new StringType(preg_replace($strPattern, $strReplace, $this->strCurrent));
            }
        }

        public function padRigtht($iSpaces, $bHtml = false)
        {
            $strBase = '';
            if ($bHtml) {
                $strBase = '<span style="text-align: right;">';
                for ($i = 1; $i <= $iSpaces; $i++) {
                    $strBase .= '&nbsp;';
                }
                $strBase .= $this->strCurrent . '</span>';
            } else {
                for ($j = 1; $j <= $iSpaces; $i++) {
                    $strBase .= ' ';
                }
                $strBase .= $this->strCurrent;
            }
            return new StringType($strBase);
        }

        public function padLeft($iSpaces, $bHtml = false)
        {
            $strBase = '';
            if ($bHtml) {
                $strBase = '<span style="text-align: left;">';
                for ($i = 1; $i <= $iSpaces; $i++) {
                    $strBase .= '&nbsp;';
                }
                $strBase .= $this->strCurrent;
            } else {
                for ($j = 1; $j <= $iSpaces; $j++) {
                    $strBase .= ' ';
                }
                $strBase .= $this->strCurrent;
            }

            return new StringType($strBase);
        }

        public function contains($strNeedle)
        {
            $strNeedle = $strNeedle instanceof StringType ? $strNeedle->__toString() : $strNeedle;

            if ($this->bMb) {
                $sEncoding = mb_detect_encoding($this->strCurrent);

                return mb_strpos(
                    $this->strCurrent,
                    mb_convert_encoding(
                        $strNeedle,
                        $sEncoding
                    )
                ) != false;
            } else {
                return strpos($this->strCurrent, $strNeedle) != false;
            }
        }

        public function compareTo($strToCompare)
        {
            $strToCompare = $strToCompare instanceof StringType
                                ? $strToCompare->__toString()
                                : $strToCompare;

            return strcmp($this->strCurrent, $strToCompare);
        }

        public function equals($sEqualTo)
        {
            if ($sEqualTo instanceof StringType) {
                $sEqualTo = $sEqualTo->__toString();
            }

            return $this->strCurrent === $sEqualTo;
        }

        protected function sanitizeMbRegex($strRegexPattern)
        {
            $strRegexPattern = $strRegexPattern instanceof StringType
                                    ? $strRegexPattern
                                    : new StringType($strRegexPattern);
            $sEncoding = mb_detect_encoding($this->strCurrent);
            mb_regex_encoding($sEncoding);
            $strRegexPattern = new StringType(
                                mb_convert_encoding(
                                    $strRegexPattern->__toString(),
                                    $sEncoding
                                )
                            );

            if (
                $strRegexPattern->startsWith('/')
                && $strRegexPattern->endsWith('/')
                && $strRegexPattern->length > 1
            ) {
                return  mb_substr(
                    $strRegexPattern->__toString(),
                    1,
                    $strRegexPattern->length - 2
                );
            } else {
                $strRegexPattern = $strRegexPattern->replace('/', '\/');
                $strRegexPattern = $strRegexPattern->replace('?', '\?');
                $strRegexPattern = $strRegexPattern->replace('(', '\(');
                $strRegexPattern = $strRegexPattern->replace(')', '\)');
                $strRegexPattern = $strRegexPattern->replace('*', '\*');
                $strRegexPattern = $strRegexPattern->replace('[', '\[');
                $strRegexPattern = $strRegexPattern->replace('{', '\{');
                $strRegexPattern = $strRegexPattern->replace('}', '\}');
                $strRegexPattern = $strRegexPattern->replace('.', '\.');
                $strRegexPattern = $strRegexPattern->replace('|', '\|');

                return $strRegexPattern->__toString();
            }

            return $strRegexPattern->startsWith('/')
                && $strRegexPattern->endsWith('/')
                && $strRegexPattern->length > 1
                    ? mb_substr($strRegexPattern->__toString(), 1, $strRegexPattern->length - 2)
                    : $strRegexPattern->replace('/', '\/')->replace('?', '\?')->__toString();
            //return $strRegexPattern->length > 1
            //	? $strRegexPattern->__toString()
            //		: $strRegexPattern->replace('/', '\/')->replace('?', '\?')->__toString();
        }
    }
}
