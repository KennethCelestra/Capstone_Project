<?php
/**
 * Application Entry Point
 */

// Load configuration
require_once __DIR__ . '/config/app.php';

// ── Error & Exception Trapping ──────────────────────────────
$logDir = ROOT_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/errors.txt');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) {
    $msg = "[" . date('Y-m-d H:i:s') . "] EXCEPTION: " . $e->getMessage()
         . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n";
    @file_put_contents(ROOT_PATH . '/storage/logs/errors.txt', $msg, FILE_APPEND);
    http_response_code(500);
    if (!headers_sent()) {
        if (file_exists(ROOT_PATH . '/app/Views/errors/500.php')) {
            require ROOT_PATH . '/app/Views/errors/500.php';
        } else {
            echo "A system error occurred. Please try again later.";
        }
    }
    exit;
});

set_error_handler(function (int $errno, string $errstr, string $file, int $line) {
    if ($errno === E_USER_ERROR || $errno === E_ERROR) {
        $msg = "[" . date('Y-m-d H:i:s') . "] ERROR [{$errno}]: {$errstr} in {$file}:{$line}\n";
        @file_put_contents(ROOT_PATH . '/storage/logs/errors.txt', $msg, FILE_APPEND);
    }
    return false; // let PHP handle non-fatal errors normally
});

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
