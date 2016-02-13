<?php
namespace Diana\Core\Mvc
{
	use Diana\Core\Std\String;
	use Diana\Core\Std\Http\Request;
	use Diana\Core\Std\Http\Response;

	class View
	{
		protected $_sTemplateFile;
		public $_sViewFile;
		protected $_response;
		protected $_request;
		protected $_sViewStack;

		public function setResponse(Response $response)
		{
			$this->_response = $response;
		}

		public function setRequest(Request $request)
		{
			$this->_request = $request;
		}

		public function getRequest()
		{
			return $this->_request;
		}

		public function getViewStack()
		{
			return $this->_sViewStack;
		}

		public function baseUri()
		{
			return $this->_request->getBaseUri() . 'web/';
		}

		public function setTemplate($sTemplate)
		{
			if ($sTemplate != null)
			{
				$sTemplate = $sTemplate instanceof String ? $sTemplate : new String($sTemplate);
				$this->_sTemplateFile = $sTemplate->endsWith('.phtml') ? $sTemplate : $sTemplate . '.phtml';
			}
			else
			{
				$this->_sTemplateFile = null;
			}
		}

		public function hasTemplate()
		{
			return !$this->_sTemplateFile == null;
		}

		public function renderView()
		{
			include($this->_sViewFile);
		}

		public function render()
		{
			$bJson = false;
			$sResponseDatatype = $this->_response->getDatatype();

			if ($sResponseDatatype->__toString() === 'json')
			{
				$bJson = true;
				ob_start();
			}

			if ($this->hasTemplate())
			{
				include($this->_sTemplateFile->__toString());
			}

			else
			{
				include($this->_sViewFile instanceof String ? $this->_sViewFile->__toString() : $this->_sViewFile);
			}

			if ($bJson)
			{
				$this->_sViewStack = new String(ob_get_contents());
				ob_end_clean();
			}
		}

		public function __call($sName, $argv)
		{
			$sFile = DIANA_CORE . 'Mvc/Helper/' . ucfirst($sName) . '.php';
			$sUserFile = DIANA_VIEWS . '_helper/' . ucfirst($sName) . '.php';

			if (file_exists($sFile))
			{
				include_once($sFile);
			}

			elseif (file_exists($sUserFile))
			{
				include_once($sUserFile);
			}

			else
			{
				throw new \Exception('File not found: ' . $sName);
			}


			return call_user_func_array($sName, $argv);
		}
	}
}
?>
