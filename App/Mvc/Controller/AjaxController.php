<?php
namespace App\Mvc\Controller
{
	use Diana\Core\Std\String;
	use Diana\Core\Mvc\BaseController;

	/**
	 * This is a simple exapmple class which shows you
	 * how you may handle Ajax Request made to the backend.
	 */
	class AjaxController extends BaseController
	{
		protected $_jsonMdl;

		public function __construct()
		{
			parent::__construct();
			$this->_view->setTemplate(null);
			$this->_jsonMdl = new JsonModel();
		}

		public function preExec()
		{
			$bAllowed = parent::preExec();

			if ($bAllowed)
			{
				$this->_response->setDatatype(new String('json'));
			}

			return $bAllowed;
		}

		public function registerform()
		{
			// no Code needed here
		}

		public function _errorHandler(\Exception $ex)
		{
			$sError = new String('An Exception has been thrown: ('
				. $ex->getCode() . ') '
				. $ex->getMessage()
				. ' in file ' . $ex->getFile()
				. ' on Line ' . $ex->getLine()
			);
			$this->_jsonMdl->addHeader(new String('error'), $sError);
			$this->_response->sendJson($this->_jsonMdl->getHeader(), $this->_jsonMdl->getBody());
		}
	}
}
?>
