<?php
namespace Diana\Core\Mvc\Init
{
	use Diana\Core\Mvc\Init\WebRequestGlueInterface;
	use Diana\Core\Std\Http\Request;
	use Diana\Core\Std\Http\Response;

	class DefaultWebRequestGlue implements WebRequestGlueInterface
	{
		protected $_request;
		protected $_response;
		protected $_outputBuffer;

		public function __construct(Request $request, Response $response)
		{
			$this->_request = $request;
			$this->_response = $response;

			ob_start('ob_gzhandler');
		}

		public function setRequest(Request $request)
		{
			$this->_request = $request;
		}

		public function setResponse(Response $response)
		{
			$this->_response = $response;
		}

        /**
         *
         * @return string the gzip compressed data
         */
		public function getResponseData()
		{
			return ob_get_clean();
		}

		/**
		 * invokes the response to be sent
		 * @return void
		 */
		public function sendResponse()
		{
			ob_end_flush();
		}
	}
}
?>
