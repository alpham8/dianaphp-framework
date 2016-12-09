<?php
// Path definitions
$paths = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
define('APP_ROOT', 'http://localhost/');
unset($paths[count($paths) - 1]);
define('ROOT_PATH', implode(DIRECTORY_SEPARATOR, $paths) . DIRECTORY_SEPARATOR);
set_include_path(ROOT_PATH);
define('DIANA_CORE', 'Lib' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR);
define('DIANA_APP', 'App' . DIRECTORY_SEPARATOR);
define('DIANA_VIEWS', DIANA_APP . 'Mvc' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
define('DIANA_MODEL', DIANA_APP . 'Mvc' . DIRECTORY_SEPARATOR. 'Model' . DIRECTORY_SEPARATOR);
define('DIANA_CONTROLLER', DIANA_APP . 'Mvc' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR);
define('DIANA_TEMPLATES', DIANA_VIEWS . '_templates' . DIRECTORY_SEPARATOR);
define('DIANA_MODULES', 'Lib' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR);
unset($paths);

// Database Connection
define('WEB_LINK', 'http://localhost/web/');
define('DB_DSN', 'mysql:host=localhost;dbname=db');
define('MYSQL_ENCODING', 'UTF8');
define('DB_USER', 'root');
define('DB_PASS', '');

define('PREG_MATCHES', 1);

define('SQL_ESC', '`');


error_reporting(E_WARNING);

// Session Handling
ini_set('session.gc_maxlifetime', 1200); // 1 hour
ini_set('session.gc_probability', 50); // 50 percent garbage collection propability
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_secure', false);
ini_set('session.use_only_cookies', true);
//test;
