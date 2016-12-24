<?php
namespace Diana\Core\Mvc
{
    use Diana\Core\Std\StringType;
    use Diana\Core\Mvc\View;
    use Diana\Core\Std\Http\Request;
    use Diana\Core\Std\Http\Response;

    class BaseController
    {
        protected $sActionName;
        protected $sControllerName;
        protected $view;
        protected $request;
        protected $response;
        protected $sView;
        protected $bViewEnabled = true;

        public function __construct()
        {
            $this->view = new View();
        }

        public function setControllerName($sControllerName)
        {
            $this->sControllerName = $sControllerName;
        }

        public function setActionName($sActionName)
        {
            $this->sActionName = $sActionName;
        }

        protected function setTemplate($sTemplate)
        {
            $this->view->setTemplate($sTemplate);
        }

        protected function disableView()
        {
            $this->bViewEnabled = false;
        }

        public function preExec()
        {
            return true;
        }

        public function afterExec()
        {
            if ($this->bViewEnabled) {
                if (empty($this->sView)) {
                    $this->view->sViewFile = ROOT_PATH . 'App' . DIRECTORY_SEPARATOR . 'Mvc'
                                            . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR
                                            . $this->sControllerName . DIRECTORY_SEPARATOR
                                            . $this->sActionName . '.phtml';
                } else {
                    $this->view->sViewFile = $this->sView;
                }

                $this->view->render();
                $sViewStack = $this->view->getViewStack();

                if ($sViewStack != null && $sViewStack instanceof StringType) {
                    $this->response->sendJson(array(), $sViewStack);
                }
            }
        }

        public function setRequest(Request $request)
        {
            $this->request = $request;
            $this->view->setRequest($request);
        }

        public function setResponse(Response $response)
        {
            $this->response = $response;
            $this->view->setResponse($this->response);
        }

        public function errorHandler(\Exception $ex)
        {
            $this->bViewEnabled = false;
            echo 'An Exception has been thrown: ('
                . $ex->getCode() . ') '
                . $ex->getMessage()
                . ' in file ' . $ex->getFile()
                . ' on Line ' . $ex->getLine();
            exit(-1);
        }
    }
}
