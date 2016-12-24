<?php
namespace Diana\Core\Mvc
{
    use Diana\Core\Std\StringType;
    use Diana\Core\Std\Http\Request;
    use Diana\Core\Std\Http\Response;

    class View
    {
        protected $sTemplateFile;
        public $sViewFile;
        protected $response;
        protected $request;
        protected $sViewStack;

        public function setResponse(Response $response)
        {
            $this->response = $response;
        }

        public function setRequest(Request $request)
        {
            $this->request = $request;
        }

        public function getRequest()
        {
            return $this->request;
        }

        public function getViewStack()
        {
            return $this->sViewStack;
        }

        public function baseUri()
        {
            return $this->request->getBaseUri() . 'web/';
        }

        public function setTemplate($sTemplate)
        {
            if ($sTemplate != null) {
                $sTemplate = $sTemplate instanceof StringType ? $sTemplate : new StringType($sTemplate);
                $this->sTemplateFile = $sTemplate->endsWith('.phtml')
                                        ? $sTemplate
                                        : $sTemplate . '.phtml';
            } else {
                $this->sTemplateFile = null;
            }
        }

        public function hasTemplate()
        {
            return !$this->sTemplateFile == null;
        }

        public function renderView()
        {
            include($this->sViewFile);
        }

        public function render()
        {
            $bJson = false;
            $sResponseDatatype = $this->response->getDatatype();

            if ($sResponseDatatype->__toString() === 'json') {
                $bJson = true;
                ob_start();
            }

            if ($this->hasTemplate()) {
                include($this->sTemplateFile->__toString());
            } else {
                include($this->sViewFile instanceof StringType
                        ? $this->sViewFile->__toString()
                        : $this->sViewFile
                );
            }

            if ($bJson) {
                $this->sViewStack = new StringType(ob_get_contents());
                ob_end_clean();
            }
        }

        public function __call($sName, $argv)
        {
            $sFile = DIANA_CORE . 'Mvc/Helper/' . ucfirst($sName) . '.php';
            $sUserFile = DIANA_VIEWS . '_helper/' . ucfirst($sName) . '.php';

            if (file_exists($sFile)) {
                include_once($sFile);
            } elseif (file_exists($sUserFile)) {
                include_once($sUserFile);
            } else {
                throw new \Exception('File not found: ' . $sName);
            }

            return call_user_func_array($sName, $argv);
        }
    }
}
