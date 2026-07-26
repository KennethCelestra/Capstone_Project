<?php
// Application Configuration

define('APP_NAME',   'ISAT U Clearance System');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUri = rtrim($scriptDir, '/') . '/';
define('BASE_URL', $protocol . '://' . $host . $baseUri);

define('ROOT_PATH',  dirname(__DIR__));

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

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '1234');
define('DB_NAME', getenv('DB_NAME') ?: 'clearance_system');

// Session Configuration
define('SESSION_NAME', getenv('SESSION_NAME') ?: 'clearance_sess');

// Mail Configuration (Brevo API)
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'kenneth.celestra@students.isatu.edu.ph');
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: 'your_brevo_api_key_here');
