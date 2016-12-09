<?php
namespace Diana\Core\Persistence\Sql
{
    use Diana\Core\Util\ExceptionView;

    class ModelException extends \Exception
    {
        public function __construct($sMessage)
        {
            parent::__construct($sMessage);
            ExceptionView::prettyPrintTrace();
        }
    }
}
