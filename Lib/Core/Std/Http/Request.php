<?php
namespace Diana\Core\Std\Http
{
    use Diana\Core\Std\String;

    class Request
    {
        const METHOD_OPTIONS  = 'OPTIONS';
        const METHOD_GET      = 'GET';
        const METHOD_HEAD     = 'HEAD';
        const METHOD_POST     = 'POST';
        const METHOD_PUT      = 'PUT';
        const METHOD_DELETE   = 'DELETE';
        const METHOD_TRACE    = 'TRACE';
        const METHOD_CONNECT  = 'CONNECT';
        const METHOD_PATCH    = 'PATCH';
        const METHOD_PROPFIND = 'PROPFIND';
        const REQUESTED_WITH_XHR = 'XMLHttpRequest';

        protected $arLanguages = array();
        protected $sPreferedLanguage;
        protected $sDocumentRoot;
        protected $arAcceptedChars = null;
        protected $bSecure;
        protected $sUserAgent;
        protected $sRemoteHost = null;
        protected $sRemoteAddr = null;
        protected $sReferer = null;
        protected $sRequestUri;
        protected $sConnection = null;
        protected $arRawCookies = array();
        protected $sRequestMethod = self::METHOD_GET;
        protected $arHeaders = array();
        protected $sBaseUri;
        protected $arAcceptEncodings = array();

        public function __construct()
        {
            if (!empty($_SERVER['HTTP_ACCEPT_CHARSET'])) {
                $s = new String($_SERVER['HTTP_ACCEPT_CHARSET']);
                $this->arAcceptedChars = $s->splitBy(';');
            }

            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                $this->sDocumentRoot = new String($_SERVER['DOCUMENT_ROOT']);
            }

            if (!empty($_SERVER['HTTP_REFERER'])) {
                $this->sReferer = new String($_SERVER['HTTP_REFERER']);
            }

            if (!empty($_SERVER['REMOTE_HOST'])) {
                $this->sRemoteHost = new String($_SERVER['REMOTE_HOST']);
            }

            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $this->sRemoteAddr = new String($_SERVER['REMOTE_ADDR']);
            }

            if (!empty($_SERVER['HTTP_CONNECTION'])) {
                $this->sConnection = new String($_SERVER['HTTP_CONNECTION']);
            }


            $this->bSecure = !empty($_SERVER['HTTPS']);
            $this->sUserAgent = new String($_SERVER['HTTP_USER_AGENT']);
            $this->sRequestUri = new String(isset($_SERVER['REQUEST_URI'])
                                    ? $_SERVER['REQUEST_URI']
                                    : $_SERVER['HTTP_REQUEST_URI']);
            $this->sRequestUri = new String('http://' . $_SERVER['HTTP_HOST']
                                    . $this->sRequestUri->__toString());

            $this->parseRawCookies();
            $this->parseAcceptLanguage();
            $this->sRequestMethod = $_SERVER['REQUEST_METHOD'];
            $this->parseHeaders();

            $sAppRoot = new String(APP_ROOT);
            $this->sBaseUri = $this->sRequestUri->substring(
                                0,
                                $this->sRequestUri->indexOf($sAppRoot) + $sAppRoot->length
                            );
            // TODO: doest not work on redirecting
//			$this->parseAcceptEncoding();

            // TODO: besseres Fallback
            \Locale::setDefault(
                checkstring($this->sPreferedLanguage)
                    ? $this->sPreferedLanguage->__toString()
                    : 'en-GB'
            );
        }

        protected function parseAcceptLanguage()
        {
            // source: http://www.thefutureoftheweb.com/blog/use-accept-language-header
            preg_match_all(
                '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
                $_SERVER['HTTP_ACCEPT_LANGUAGE'],
                $this->arLanguages
            );

            if (count($this->arLanguages[1])) {
                // create a list like "en" => 0.8
                $this->arLanguages = array_combine($this->arLanguages[1], $this->arLanguages[4]);

                // set default to 1 for any without q factor
                foreach ($this->arLanguages as $lang => $val) {
                    if ($val === '') {
                        $this->arLanguages[$lang] = 1;
                    }
                }

                // sort list based on value
                arsort($this->arLanguages, SORT_NUMERIC);

                $iHighestQualifier = 0.0;
                foreach ($this->arLanguages as $sLang => $iQaulifier) {
                    if ($iHighestQualifier === 1.0) {
                        $this->sPreferedLanguage = new String($sLang);
                        break;
                    }

                    if ($iHighestQualifier < $iQaulifier) {
                        $iHighestQualifier = $iQaulifier;
                        $this->sPreferedLanguage = new String($sLang);
                    }
                }
            }
        }

        protected function parseRawCookies()
        {
            if (isset($_SERVER['HTTP_COOKIE'])) {
                $s = new String($_SERVER['HTTP_COOKIE']);
                foreach ($s->splitBy(';') as $rawcookie) {
                    $ss = new String($rawcookie);
                    list($k, $v) = $ss->splitBy('=', 2);
                    $this->arRawCookies[$k] = $v;
                }
            }
        }

        protected function parseHeaders()
        {
            $arHeaders = headers_list();
            $arSingleH = array();
            foreach ($arHeaders as $sHeader) {
                $sHeader = new String($sHeader);
                $arSingleH = $sHeader->splitToStringsBy(':');

                if (count($arSingleH) === 2) {
                    $this->arHeaders[$arSingleH[0]
                        ->trim()
                        ->__toString()] = $arSingleH[1]
                                            ->trim()
                                            ->__toString();
                }
            }
        }

        public function isGet()
        {
            return $this->sRequestMethod === self::METHOD_GET;
        }

        public function isPost()
        {
            return $this->sRequestMethod === self::METHOD_POST;
        }

        public function isXHR()
        {
            return isset(
                $this->arHeaders['X_REQUESTED_WITH']
            )
                && $this->arHeaders['X_REQUESTED_WITH'] === self::REQUESTED_WITH_XHR;
        }

        public function isPatch()
        {
            return $this->sRequestMethod === self::METHOD_PATCH;
        }

        public function isFlashRequest()
        {
            return stristr($this->arHeaders('USER_AGENT'), ' flash') !== false;
        }

        public function isConnect()
        {
            return $this->sRequestMethod === self::METHOD_CONNECT;
        }

        public function isTrace()
        {
            return $this->sRequestMethod === self::METHOD_TRACE;
        }

        public function isDelete()
        {
            return $this->sRequestMethod == self::METHOD_DELETE;
        }

        public function isPut()
        {
            return $this->sRequestMethod === self::METHOD_PUT;
        }

        public function getParam(String $sKey)
        {
            $sVal = $this->getRawParam($sKey);

            if (
                $sVal === null
                && $sVal instanceof String === false
            ) {
                return null;
            }

            $esc = new Escaper($sVal);
            $esc->escapeAll();
            return $esc->getEscaped();
        }

        public function getRawParam(String $sKey)
        {
            $sRet = null;
            if ($this->isGet()) {
                if (array_key_exists($sKey->__toString(), $_GET)) {
                    return new String($_GET[$sKey->__toString()]);
                }

                if (
                    $this->sRequestUri->contains('/' . $sKey . '=')
                    || $this->sRequestUri->contains('/' . $sKey . urlencode('='))
                ) {
                    $sKey = new String('/' . $sKey->__toString());
                    $sBegin = $this->sRequestUri->substring($this->sRequestUri->indexOf($sKey) + 1);
                    $iEnd = $sBegin->indexOf('/');
                    $iEquals = $sBegin->indexOf('=');

                    if (!$iEquals) {
                        $sEquals = new String(urlencode('='));
                        $iEquals = $sBegin->indexOf($sEquals) ? $sBegin->indexOf($sEquals) + $sEquals->length - 1 : false;
                    }

                    if ($iEquals) {
                        if (!$iEnd) {
                            $sRet = new String(urldecode($sBegin->substring($iEquals + 1)->__toString()));
                        } else {
                            $sRet = new String(urldecode($sBegin->substring($iEquals + 1, $iEnd)->__toString()));
                        }
                    }
                }
            } elseif ($this->isPost()) {
                if (array_key_exists($sKey->__toString(), $_POST)) {
                    $sRet = new String($_POST[$sKey->__toString()]);
                }
            }

            return $sRet;
        }

        public function getAllParams()
        {
            $arRet = array();
            if ($this->isGet()) {
                if (
                    is_array($_GET)
                    && count($_GET) > 0
                ) {
                    foreach ($_GET as $strKey => $strVal) {
                        $arRet[$strKey] = new String($strVal);
                    }

                    return $arRet;
                }

                $sParams = $this->sRequestUri->substring($this->sBaseUri->length);
                $arParamsUnsorted = $sParams->splitToStringsBy('/');

                foreach ($arParamsUnsorted as $sKeyValue) {
                    $arSingleWalker = $sKeyValue->splitBy('=');

                    if (count($arSingleWalker) === 2) {
                        $arRet[$arSingleWalker[0]] = $arSingleWalker[1];
                    }
                }
            } elseif ($this->isPost()) {
                foreach ($_POST as $strKey => $strVal) {
                    $arRet[$strKey] = new String($strVal);
                }
            }

            return $arRet;
        }

        public function getBaseUri()
        {
            return $this->sBaseUri;
        }

        public function getRequestUri()
        {
            return $this->sRequestUri;
        }

        public function parseAcceptEncoding()
        {
            function sortQuantities($old, $new)
            {
                return strcmp($old, $new);
            }

            $arBuilder = array();
            // Test-Case below
            //preg_match_all('/\w+(;q=\d{1}\.\d{1}|(?=,))/x', 'gzip;q=1.0, deflate;q=0.8, lzma, sdch', $arBuilder);
            preg_match_all(
                '/\w+(;q=\d{1}\.\d{1}|(?=,))/x',
                $_SERVER['HTTP_ACCEPT_ENCODING'],
                $arBuilder
            );

            // it have quantities, sort them
            if (count($arBuilder) === 2) {
                $this->arAcceptEncodings = array();

                if (empty($arBulder[1][0])) {
                    // take the first element and give an quantity of 1.0
                    $sVal = new String($arBuilder[0][0]);
                    $this->arAcceptEncodings = array('1.0' => $sVal);
                } else {
                    foreach ($arBuilder[1] as $index => $value) {
                        // remove semicolon at the beginng of the key.
                        // No need for looping twice over it
                        $sVal = new String($arBuilder[0][$index]);
                        $this
                            ->arAcceptEncodings[substr($value, 3)] = $sVal
                                                                        ->substring(
                                                                            0,
                                                                            $sVal->indexOf(';')
                                                                        );
                    }

                    uasort(
                        $this->arAcceptEncodings,
                        array("\\Diana\\Core\\Std\\Http\\Request", 'sortQuantities')
                    );
                }
            }
        }

        protected function sortQuantities($sOld, $sNew)
        {
            // nice and smooth String class handling ;-)
            return $sNew->compareTo($sOld);
        }

        public function getAcceptEncodings()
        {
            if (empty($this->arAcceptEncodings)) {
                return false;
            }

            return $this->arAcceptEncodings;
        }
    }
}
