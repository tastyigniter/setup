<?php

// Constants
define('TI_PHP_VERSION', '7.4');
define('TI_MYSQL_VERSION', '5.7');
define('SETUPPATH', __DIR__);
define('BASEPATH', dirname(SETUPPATH));
define('PARTIALPATH', SETUPPATH.'/partials/');

/*
 * Check PHP version
 */
if (version_compare(PHP_VERSION, TI_PHP_VERSION, '<')) exit('You need at least PHP '.TI_PHP_VERSION.' to install TastyIgniter using this setup wizard.');

// PHP headers
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Session
session_start();

// Debug mode
$isDebug = array_key_exists('debug', $_REQUEST);
if ($isDebug) {
    define('ENVIRONMENT', 'development');
    ini_set('display_errors', 1);
    error_reporting(-1);
}
else {
    define('ENVIRONMENT', 'production');
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Exception handler
register_shutdown_function('installerShutdown');
function installerShutdown()
{
    global $setup;
    $error = error_get_last();
    if (isset($error['type']) && $error['type'] == 1) {
        header('HTTP/1.1 500 Internal Server Error');
        $errorMsg = htmlspecialchars_decode(strip_tags($error['message']));
        echo $errorMsg;
        if (isset($setup)) {
            $setup->writeLog('Fatal error: %s on line %s in file %s', $errorMsg, $error['line'], $error['file']);
        }
        exit;
    }
}

require_once 'language/en/default.php';
require_once 'classes/SetupPDO.php';
require_once 'classes/SetupException.php';
require_once 'classes/SetupRepository.php';
require_once 'classes/SetupController.php';

try {
    $setup = new SetupController();
    $setup->cleanLog();
    $setup->writeLog('Host: %s', php_uname());
    $setup->writeLog('PHP version: %s', PHP_VERSION);
    $setup->writeLog('Server software: %s', $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown');
    $setup->writeLog('Operating system: %s', PHP_OS);
    $setup->writeLog('Memory limit: %s', ini_get('memory_limit'));
    $setup->writeLog('Max execution time: %s', ini_get('max_execution_time'));

    $page = $setup->getPage();
}
catch (Exception $ex) {
    $fatalError = $ex->getMessage();
}
