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

		protected $_arLanguages = array();
		protected $_sPreferedLanguage;
		protected $_sDocumentRoot;
		protected $_arAcceptedChars = null;
		protected $_bSecure;
		protected $_sUserAgent;
		protected $_sRemoteHost = null;
		protected $_sRemoteAddr = null;
		protected $_sReferer = null;
		protected $_sRequestUri;
		protected $_sConnection = null;
		protected $_arRawCookies = array();
		protected $_sRequestMethod = self::METHOD_GET;
		protected $_arHeaders = array();
		protected $_sBaseUri;
		protected $_arAcceptEncodings = array();

		public function __construct()
		{
			if (!empty($_SERVER['HTTP_ACCEPT_CHARSET']))
			{
				$s = new String($_SERVER['HTTP_ACCEPT_CHARSET']);
				$this->_arAcceptedChars = $s->splitBy(';');
			}

			if (!empty($_SERVER['DOCUMENT_ROOT']))
			{
				$this->_sDocumentRoot = new String($_SERVER['DOCUMENT_ROOT']);
			}

			if (!empty($_SERVER['HTTP_REFERER']))
			{
				$this->_sReferer = new String($_SERVER['HTTP_REFERER']);
			}

			if (!empty($_SERVER['REMOTE_HOST']))
			{
				$this->_sRemoteHost = new String($_SERVER['REMOTE_HOST']);
			}

			if (!empty($_SERVER['REMOTE_ADDR']))
			{
				$this->_sRemoteAddr = new String($_SERVER['REMOTE_ADDR']);
			}

			if (!empty($_SERVER['HTTP_CONNECTION']))
			{
				$this->_sConnection = new String($_SERVER['HTTP_CONNECTION']);
			}


			$this->_bSecure = !empty($_SERVER['HTTPS']);
			$this->_sUserAgent = new String($_SERVER['HTTP_USER_AGENT']);
			$this->_sRequestUri = new String(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_REQUEST_URI']);
			$this->_sRequestUri = new String('http://' . $_SERVER['HTTP_HOST'] . $this->_sRequestUri->__toString());

			$this->_parseRawCookies();
			$this->_parseAcceptLanguage();
			$this->_sRequestMethod = $_SERVER['REQUEST_METHOD'];
			$this->_parseHeaders();

			$sAppRoot = new String(APP_ROOT);
			$this->_sBaseUri = $this->_sRequestUri->substring(0, $this->_sRequestUri->indexOf($sAppRoot) + $sAppRoot->length);
			// TODO: doest not work on redirecting
//			$this->parseAcceptEncoding();

			// TODO: besseres Fallback
			\Locale::setDefault(checkstring($this->_sPreferedLanguage) ? $this->_sPreferedLanguage->__toString() : 'en-GB');
		}

		protected function _parseAcceptLanguage()
		{
			// source: http://www.thefutureoftheweb.com/blog/use-accept-language-header
			preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $this->_arLanguages);
			if (count($this->_arLanguages[1]))
			{
				// create a list like "en" => 0.8
				$this->_arLanguages = array_combine($this->_arLanguages[1], $this->_arLanguages[4]);

				// set default to 1 for any without q factor
				foreach ($this->_arLanguages as $lang => $val)
				{
					if ($val === '') $this->_arLanguages[$lang] = 1;
				}

				// sort list based on value
				arsort($this->_arLanguages, SORT_NUMERIC);

				$iHighestQualifier = 0.0;
				foreach ($this->_arLanguages as $sLang => $iQaulifier)
				{
					if ($iHighestQualifier === 1.0)
					{
						$this->_sPreferedLanguage = new String($sLang);
						break;
					}

					if ($iHighestQualifier < $iQaulifier)
					{
						$iHighestQualifier = $iQaulifier;
						$this->_sPreferedLanguage = new String($sLang);
					}
				}
			}
		}

		protected function _parseRawCookies()
		{
			if (isset($_SERVER['HTTP_COOKIE']))
			{
				$s = new String($_SERVER['HTTP_COOKIE']);
				foreach($s->splitBy(';') as $rawcookie)
				{
					$ss = new String($rawcookie);
					list($k,$v) = $ss->splitBy('=', 2);
					$this->_arRawCookies[$k] = $v;
				}
			}
		}

		protected function _parseHeaders()
		{
			$arHeaders = headers_list();
			$arSingleH;
			foreach ($arHeaders as $sHeader)
			{
				$sHeader = new String($sHeader);
				$arSingleH = $sHeader->splitToStringsBy(':');
				if (count($arSingleH) === 2)
				{
					$this->_arHeaders[$arSingleH[0]->trim()->__toString()] = $arSingleH[1]->trim()->__toString();
				}
			}
		}

		public function isGet()
		{
			return $this->_sRequestMethod === self::METHOD_GET;
		}

		public function isPost()
		{
			return $this->_sRequestMethod === self::METHOD_POST;
		}

		public function isXHR()
		{
			return isset($this->_arHeaders['X_REQUESTED_WITH']) && $this->_arHeaders['X_REQUESTED_WITH'] === self::REQUESTED_WITH_XHR;
		}

		public function isPatch()
		{
			return $this->_sRequestMethod === self::METHOD_PATCH;
		}

		public function isFlashRequest()
		{
			return stristr($this->_arHeaders('USER_AGENT'), ' flash') !== false;
		}

		public function isConnect()
		{
			return $this->_sRequestMethod === self::METHOD_CONNECT;
		}

		public function isTrace()
		{
			return $this->_sRequestMethod === self::METHOD_TRACE;
		}

		public function isDelete()
		{
			return $this->_sRequestMethod == self::METHOD_DELETE;
		}

		public function isPut()
		{
			return $this->_sRequestMethod === self::METHOD_PUT;
		}

		public function getParam(String $sKey)
		{
			$sVal = $this->getRawParam($sKey);

			if ($sVal === null && $sVal instanceof String === false)
			{
				return null;
			}

			$esc = new Escaper($sVal);
			$esc->escapeAll();
			return $esc->getEscaped();
		}

		public function getRawParam(String $sKey)
		{
			$sRet = null;
			if ($this->isGet())
			{
				if (array_key_exists($sKey->__toString(), $_GET))
				{
					return new String($_GET[$sKey->__toString()]);
				}

				if ($this->_sRequestUri->contains('/' . $sKey . '=')
					|| $this->_sRequestUri->contains('/' . $sKey . urlencode('=')))
				{
					$sKey = new String('/' . $sKey->__toString());
					$sBegin = $this->_sRequestUri->substring($this->_sRequestUri->indexOf($sKey) + 1);
					$iEnd = $sBegin->indexOf('/');

					$iEquals = $sBegin->indexOf('=');
					if (!$iEquals)
					{
						$sEquals = new String(urlencode('='));
						$iEquals = $sBegin->indexOf($sEquals) ? $sBegin->indexOf($sEquals) + $sEquals->length - 1 : false;
					}

					if ($iEquals)
					{
						if (!$iEnd)
						{
							$sRet = new String(urldecode($sBegin->substring($iEquals + 1)->__toString()));
						}

						else
						{
							$sRet = new String(urldecode($sBegin->substring($iEquals + 1, $iEnd)->__toString()));
						}
					}
				}
			}

			elseif ($this->isPost())
			{
				if (array_key_exists($sKey->__toString(), $_POST))
				{
					$sRet = new String($_POST[$sKey->__toString()]);
				}
			}

			return $sRet;
		}

		public function getAllParams()
		{
			$arRet = array();
			if ($this->isGet())
			{
				if (is_array($_GET) && count($_GET) > 0)
				{
					foreach ($_GET as $strKey => $strVal)
					{
						$arRet[$strKey] = new String($strVal);
					}

					return $arRet;
				}

				$sParams = $this->_sRequestUri->substring($this->_sBaseUri->length);
				$arParamsUnsorted = $sParams->splitToStringsBy('/');

				foreach ($arParamsUnsorted as $sKeyValue)
				{
					$arSingleWalker = $sKeyValue->splitBy('=');

					if (count($arSingleWalker) === 2)
					{
						$arRet[$arSingleWalker[0]] = $arSingleWalker[1];
					}
				}
			}

			elseif ($this->isPost())
			{
				foreach ($_POST as $strKey => $strVal)
				{
					$arRet[$strKey] = new String($strVal);
				}
			}

			return $arRet;
		}

		public function getBaseUri()
		{
			return $this->_sBaseUri;
		}

		public function getRequestUri()
		{
			return $this->_sRequestUri;
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
			preg_match_all('/\w+(;q=\d{1}\.\d{1}|(?=,))/x', $_SERVER['HTTP_ACCEPT_ENCODING'], $arBuilder);


			// it have quantities, sort them
			if (count($arBuilder) === 2)
			{
				$this->_arAcceptEncodings = array();

				if (empty($arBulder[1][0]))
				{
					// take the first element and give an quantity of 1.0
					$sVal = new String($arBuilder[0][0]);
					$this->_arAcceptEncodings = array('1.0' => $sVal);
				}

				else
				{
					foreach ($arBuilder[1] as $index => $value)
					{
						// remove semicolon at the beginng of the key. No need for looping twice over it
						$sVal = new String($arBuilder[0][$index]);
						$this->_arAcceptEncodings[substr($value, 3)] = $sVal->substring(0, $sVal->indexOf(';'));
					}

					uasort($this->_arAcceptEncodings, array("\\Diana\\Core\Std\\Http\\Request", '_sortQuantities'));
				}
			}
		}

		protected function _sortQuantities($sOld, $sNew)
		{
			// nice and smooth String class handling ;-)
			return $sNew->compareTo($Old);
		}

		public function getAcceptEncodings()
		{
			if (empty($this->_arAcceptEncodings))
			{
				return false;
			}

			return $this->_arAcceptEncodings;
		}
	}
}
?>
