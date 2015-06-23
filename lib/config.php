<?php
// Path definitions
$paths = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
define('APP_ROOT', 'http://localhost/clanscript20/');
unset($paths[count($paths) - 1]);
define('ROOT_PATH', implode('/', $paths) . '/');
set_include_path(ROOT_PATH);
define('EPHP_CORE', 'lib/core/');
define('EPHP_APP', 'app/');
define('EPHP_VIEWS', EPHP_APP . 'mvc/views/');
define('EPHP_MODEL', EPHP_APP . 'mvc/model/');
define('EPHP_CONTROLLER', EPHP_APP . 'mvc/controller/');
define('EPHP_TEMPLATES', EPHP_VIEWS . '_templates/');
define('EPHP_MODULES', 'lib/modules/');
unset($paths);

// Database Connection
define('WEB_LINK', 'http://localhost/clanscript20/web/');
define('DB_DSN', 'mysql:host=localhost;dbname=clanscript20');
define('DB_USER', 'root');
define('DB_PASS', '');

define('PREG_MATCHES', 1);

define('SQL_ESC', '`');

error_reporting(E_WARNING);

// Session Handling
ini_set('session.gc_maxlifetime', 1200); // 1 hour
ini_set('session.gc_probability', 50); // 50 percent garbage collection propability
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_secure', FALSE);
ini_set('session.use_only_cookies', TRUE);
//test
?>
