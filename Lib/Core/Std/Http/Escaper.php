<?php
namespace Diana\Core\Std\Http
{
    use Diana\Core\Std\StringType;

    class Escaper
    {
        protected $sRaw;

        public function __construct(StringType $sRaw)
        {
            $this->sRaw = $sRaw;
        }

        public function stripTags()
        {
            $this->sRaw = new StringType(\strip_tags($this->sRaw->__toString()));
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
