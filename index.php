<?php
/**
 * Application Entry Point
 */

// Load configuration
require_once __DIR__ . '/config/app.php';

// Autoload core classes
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Router.php';

// Start session
session_name(SESSION_NAME);
$isHttps = (
    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Load routes
require_once ROOT_PATH . '/config/routes.php';

// Dispatch
$router = new Router();
$router->loadRoutes($routes);
$router->dispatch();
