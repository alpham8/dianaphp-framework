<?php
namespace Diana\Core\Mvc
{
	use Diana\Core\Std\String;
	use Diana\Core\Mvc\View;
	use Diana\Core\Std\Http\Request;
	use Diana\Core\Std\Http\Response;

	class BaseController
	{
		protected $_sActionName;
		protected $_sControllerName;
		protected $_view;
		protected $_request;
		protected $_response;
		protected $_sView;
		protected $_bViewEnabled = true;

		public function __construct()
		{
			$this->_view = new View();
		}

		public function setControllerName($sControllerName)
		{
			$this->_sControllerName = $sControllerName;
		}

		public function setActionName($sActionName)
		{
			$this->_sActionName = $sActionName;
		}

		protected function _setTemplate($sTemplate)
		{
			$this->_view->setTemplate($sTemplate);
		}

		protected function _disableView()
		{
			$this->_bViewEnabled = false;
		}

		public function preExec()
		{
			return true;
		}

		public function afterExec()
		{
			if ($this->_bViewEnabled)
			{
				if (empty($this->_sView))
				{
					$this->_view->_sViewFile = ROOT_PATH . 'app/mvc/views/' . $this->_sControllerName . '/' . $this->_sActionName . '.phtml';
				}

				else
				{
					$this->_view->_sViewFile = $this->_sView;
				}

				$this->_view->render();
				$sViewStack = $this->_view->getViewStack();

				if ($sViewStack != null && $sViewStack instanceof String)
				{
					$this->_response->sendJson(array(), $sViewStack);
				}
			}
		}

		public function setRequest(Request $request)
		{
			$this->_request = $request;
			$this->_view->setRequest($request);
		}

		public function setResponse(Response $response)
		{
			$this->_response = $response;
			$this->_view->setResponse($this->_response);
		}

		public function _errorHandler(\Exception $ex)
		{
			$this->_bViewEnabled = false;
			echo 'An Exception has been thrown: ('
				. $ex->getCode() . ') '
				. $ex->getMessage()
				. ' in file ' . $ex->getFile()
				. ' on Line ' . $ex->getLine();
			exit(-1);
		}
	}
}
?>
