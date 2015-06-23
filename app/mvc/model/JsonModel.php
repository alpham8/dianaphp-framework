<?php
class JsonModel
{
	protected $_sBody;
	protected $_arHeader = array();

	public function __construct()
	{
		$this->_sBody = new String();
	}

	public function getBody()
	{
		return  $this->_sBody;
	}

	public function setBody($sBody)
	{
		$this->_sBody = $sBody;
	}

	public function getHeader()
	{
		return $this->_arHeader;
	}

	public function addHeader(String $sKey, String $sValue)
	{
		$this->_arHeader[$sKey->__toString()] = $sValue;
	}
}
?>
