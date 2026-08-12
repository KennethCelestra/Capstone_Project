<?php
// Application Configuration

define('ROOT_PATH', dirname(__DIR__));

// Load .env file if present
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B'\"");
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// Helper function to fetch env values across $_ENV, $_SERVER, and getenv()
function get_env_var(string $key, string $default = ''): string
{
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    $val = getenv($key);
    return ($val !== false && $val !== '') ? $val : $default;
}

define('APP_NAME', 'AutoClear Clearance System');
define('APP_ENV',  get_env_var('APP_ENV', 'production'));

// Configure PHP Error Handling based on APP_ENV
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('log_errors', '1');
    $logDir = ROOT_PATH . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/error.log');
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Base URL calculation (Supports APP_URL override and HTTPS proxy/Cloudflare detection)
$appUrl = trim(get_env_var('APP_URL'));
if (!empty($appUrl)) {
    define('BASE_URL', rtrim($appUrl, '/') . '/');
} else {
    $isHttps = (
        (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );
    $protocol  = $isHttps ? "https" : "http";
    $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $baseUri   = rtrim($scriptDir, '/') . '/';
    define('BASE_URL', $protocol . '://' . $host . $baseUri);
}

// Database Configuration
define('DB_HOST', get_env_var('DB_HOST', 'localhost'));
define('DB_USER', get_env_var('DB_USER', 'root'));
define('DB_PASS', get_env_var('DB_PASS', ''));
define('DB_NAME', get_env_var('DB_NAME', 'clearance_system'));

// Session Configuration
define('SESSION_NAME', get_env_var('SESSION_NAME', 'clearance_sess'));

// Mail Configuration (Brevo API)
define('MAIL_FROM', get_env_var('MAIL_FROM', 'kenneth.celestra@students.isatu.edu.ph'));
define('BREVO_API_KEY', get_env_var('BREVO_API_KEY', 'your_brevo_api_key_here'));

// Clearance Form Token (HMAC signing key)
define('APP_SECRET', get_env_var('APP_SECRET', 'isatu_clr_fallback_dev_secret_change_me'));
