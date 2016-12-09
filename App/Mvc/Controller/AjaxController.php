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
        protected $jsonMdl;

        public function __construct()
        {
            parent::__construct();
            $this->view->setTemplate(null);
            $this->jsonMdl = new JsonModel();
        }

        public function preExec()
        {
            $bAllowed = parent::preExec();

            if ($bAllowed) {
                $this->response->setDatatype(new String('json'));
            }

            return $bAllowed;
        }

        public function registerform()
        {
            // no Code needed here
        }

        public function errorHandler(\Exception $ex)
        {
            $sError = new String('An Exception has been thrown: ('
                . $ex->getCode() . ') '
                . $ex->getMessage()
                . ' in file ' . $ex->getFile()
                . ' on Line ' . $ex->getLine()
            );
            $this->jsonMdl->addHeader(new String('error'), $sError);
            $this->response->sendJson($this->jsonMdl->getHeader(), $this->jsonMdl->getBody());
        }
    }
}
