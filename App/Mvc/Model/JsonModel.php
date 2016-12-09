<?php
namespace App\Mvc\Model
{
    use Diana\Core\Std\String;

    /**
     * A custom model for handling JSON data.
     * As in the MVC context this is "strictly seen" also a model.
     */
    class JsonModel
    {
        protected $sBody;
        protected $arHeader = array();

        public function __construct()
        {
            $this->sBody = new String();
        }

        public function getBody()
        {
            return  $this->sBody;
        }

        public function setBody($sBody)
        {
            $this->sBody = $sBody;
        }

        public function getHeader()
        {
            return $this->arHeader;
        }

        public function addHeader(String $sKey, String $sValue)
        {
            $this->arHeader[$sKey->__toString()] = $sValue;
        }
    }
}
