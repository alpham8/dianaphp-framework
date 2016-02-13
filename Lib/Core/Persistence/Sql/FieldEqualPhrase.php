<?php
class FieldEqualPhrase
{
	protected $_sCurrent = '';

	public function addString(String $sName, String $sValue)
	{
		$sName = $sName->__toString();
		$sValueOld = $sValue;
		$sValue = $sValue->__toString();

		if ($sValueOld->isEmpty())
		{
			$this->_sCurrent .= <<<SQL
 $sName = null,
SQL;
		}
		else
		{
			$this->_sCurrent .= <<<SQL
 $sName = '$sValue',
SQL;
		}
	}

	public function addInt(String $sName, $iVal)
	{
		$sName = $sName->__toString();
		if (empty($iVal))
		{
			$this->_sCurrent .= <<<SQL
 $sName = null,
SQL;
		}

		else
		{
			$this->_sCurrent .= <<<SQL
 $sName = $iVal,
SQL;
		}
	}

	public function hasFields()
	{
		$sCur = new String($this->_sCurrent);

		return (int)$sCur->matches('/\w\s*=\s*.+/') === PREG_MATCHES;
	}

	public function __toString()
	{
		$s = new String($this->_sCurrent);
		$s = $s->trim();
		$s = $s->substring(0, $s->length - 1);

		return $s->__toString();
	}
}
?>
