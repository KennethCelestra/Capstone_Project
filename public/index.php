<?php
/**
 * Application Entry Point
 */

// Load configuration
require_once dirname(__DIR__) . '/config/app.php';

// Autoload core classes
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Router.php';

// Start session
session_name(SESSION_NAME);
session_start();

// Load routes
require_once ROOT_PATH . '/config/routes.php';

// Dispatch
$router = new Router();
$router->loadRoutes($routes);
$router->dispatch();
