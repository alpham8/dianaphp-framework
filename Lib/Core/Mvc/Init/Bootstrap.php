<?php
namespace Diana\Core\Mvc\Init
{
    use Diana\Core\Std\String;
    use Diana\Core\Mvc\Init\Dispatcher;

    // Localize Langauge for Date settings
    // FF on Linux: de-de,de;q=0.8,en-us;q=0.5,en;q=0.3

    class Bootstrap
    {
        public function init()
        {
            $sRequestUri = new String(isset($_SERVER['REQUEST_URI'])
                            ? $_SERVER['REQUEST_URI']
                            : $_SERVER['HTTP_REQUEST_URI']);
            $sRequestUri = new String('http://' . $_SERVER['HTTP_HOST']
                            . $sRequestUri->__toString());

            Dispatcher::init($sRequestUri);
            Dispatcher::dispatch();
        }
    }
}
