<?php
require_once_file(EPHP_CORE . 'Util/ExceptionView.php');

class ModelException extends Exception
{
	public function __construct($sMessage)
	{
		parent::__construct($sMessage);
		ExceptionView::prettyPrintTrace();
	}
}
?>
