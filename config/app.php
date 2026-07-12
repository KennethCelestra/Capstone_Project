<?php
// Application Configuration

define('APP_NAME',   'ISAT U Clearance System');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUri = rtrim($scriptDir, '/') . '/';
define('BASE_URL', $protocol . '://' . $host . $baseUri);

define('ROOT_PATH',  dirname(__DIR__));

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '1234');
define('DB_NAME', 'clearance_system');

// Session Configuration
define('SESSION_NAME', 'clearance_sess');

// Mail Configuration (Gmail SMTP)
define('MAIL_FROM', 'kencelestra637@gmail.com');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'kencelestra637@gmail.com');
define('SMTP_PASS', 'snnbxkenoztxvotn');
define('SMTP_PORT', 587);
