<?php
namespace Diana\Core\Std\Http
{
	use Diana\Core\Std\String;

	class Escaper
	{
		protected $_sRaw;

		public function __construct(String $sRaw)
		{
			$this->_sRaw = $sRaw;
		}

		public function stripTags()
		{
			$this->_sRaw = new String(\strip_tags($this->_sRaw->__toString()));
		}

		public function escapeAll()
		{
			$this->stripTags();
		}

		public function getEscaped()
		{
			return $this->_sRaw;
		}
	}
}
?>