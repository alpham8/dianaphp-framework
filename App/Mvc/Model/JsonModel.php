<?php
namespace App\Mvc\Model
{
    use Diana\Core\Std\StringType;

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
            $this->sBody = new StringType();
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

        public function addHeader(StringType $sKey, StringType $sValue)
        {
            $this->arHeader[$sKey->__toString()] = $sValue;
        }
    }
}
