<?php

session_start();

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);


/**
 * Compilation SCSS to CSS
 */
use ScssPhp\ScssPhp\Compiler;

try {
    $compiler = new Compiler();
    $compiler->addImportPath('scss');
    $scss_string = file_get_contents('scss/index.scss');
    $result = $compiler->compileString($scss_string);
    file_put_contents('css/main.css', $result->getCss());
} catch (\Exception $e) {
    syslog(LOG_ERR, 'scssphp: Unable to compile content');
}
