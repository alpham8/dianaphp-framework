<?php
namespace Diana\Core\Std
{
	use Diana\Core\Std\String;

	class Email
	{
		const M_TYPE_TEXT = 'text';
		const M_TYPE_HTML = 'text/html';
		const NO_SUBJECT = '<<No Subjcet>>';

		protected $_sType;
		protected $_sFrom;
		protected $_sRcpt;
		protected $_arCC;
		protected $_arBCC;
		protected $_sReplyTo;
		protected $_sSubject;
		protected $_sBody;

		public function __construct()
		{
			$this->_sType = new String(self::M_TYPE_TEXT);
		}

		public function setFrom(String $sFrom)
		{
			$this->_sFrom = $sFrom;
		}

		public function setRcpt(String $sRcpt)
		{
			$this->_sRcpt = $sRcpt;
		}

		public function addCC(String $sCC)
		{
			$this->_arBCC[] = $sCC;
		}

		public function addBCC(String $sBCC)
		{
			$this->_arBCC[] = $sBCC;
		}

		public function setSubject(String $sSubject)
		{
			$this->_sSubject = $sSubject;
		}

		public function getSubject()
		{
			return isset($this->_sSubject) && checkstring($this->_sSubject) ? $this->_sSubject : new String(self::NO_SUBJECT);
		}

		public function setBody(String $sBody)
		{
			$this->_sBody = $sBody;
		}

		public function setReplyTo(String $sReplyTo)
		{
			$this->_sReplyTo = $sReplyTo;
		}

		protected function _check()
		{
			return checkstring($this->_sFrom)  && checkstring($this->_sRcpt) && checkstring($this->_sBody);
		}

		public function send()
		{
			if ($this->_check())
			{

				$sHeaders = "MIME-Version: 1.0\r\n";

				if ($this->_sType->equals(self::M_TYPE_TEXT))
				{
					$sHeaders .= "Content-Type:text/plain;charset=UTF-8\r\n";
				}

				else
				{
					$sHeaders .= "Content-Type:text/html;charset=UTF-8\r\n";
				}

				$sHeaders .= 'From: ' . $this->_sFrom . "\r\n";

				if (checkstring($this->_sReplyTo))
				{
					$sHeaders .= 'Reply-To: ' . $this->_sReplyTo . "\r\n";
				}

				if (array_filled($this->_arCC))
				{
					$sHeaders .= 'CC: ' . implode(', ', $this->_arCC) . "\r\n";
				}

				if (array_filled($this->_arBCC))
				{
					$sHeaders .= 'BCC: ' . implode(', ', $this->_arBCC) . "\r\n";
				}

				mail($this->_sRcpt->__toString(), $this->getSubject()->__toString(), $this->_sBody->__toString(), $sHeaders);

				return true;
			}

			return false;
		}
	}
}
?>
