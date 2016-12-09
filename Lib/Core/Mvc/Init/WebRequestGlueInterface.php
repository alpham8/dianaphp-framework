<?php
namespace Diana\Core\Mvc\Init
{
    use Diana\Core\Std\Http\Request;
    use Diana\Core\Std\Http\Response;

    interface WebRequestGlueInterface
    {
        public function setRequest(Request $repquest);

        public function setResponse(Response $response);

        public function getResponseData();
    }
}
