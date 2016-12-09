<?php
namespace Diana\Core\Std\Http
{
    use Diana\Core\Std\String;

    class Escaper
    {
        protected $sRaw;

        public function __construct(String $sRaw)
        {
            $this->sRaw = $sRaw;
        }

        public function stripTags()
        {
            $this->sRaw = new String(\strip_tags($this->sRaw->__toString()));
        }

        public function escapeAll()
        {
            $this->stripTags();
        }

        public function getEscaped()
        {
            return $this->sRaw;
        }
    }
}
