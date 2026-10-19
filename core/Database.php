<?php

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $port = (defined('DB_PORT') && DB_PORT !== '') ? ';port=' . DB_PORT : '';
            $dsn = 'mysql:host=' . DB_HOST . $port . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                // Auto-enable SSL/TLS if CA certificates exist (required by TiDB Cloud & Aiven)
                if (defined('PDO::MYSQL_ATTR_SSL_CA') && file_exists('/etc/ssl/certs/ca-certificates.crt')) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                } elseif (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT') && DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1') {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    die('Database connection failed: ' . $e->getMessage());
                } else {
                    http_response_code(500);
                    $errorView = ROOT_PATH . '/app/Views/errors/500.php';
                    if (file_exists($errorView)) {
                        require_once $errorView;
                    } else {
                        echo '<!DOCTYPE html><html><head><title>500 Internal Error</title></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h1>500 Internal Server Error</h1><p>A database connection error occurred. Please contact the administrator.</p></body></html>';
                    }
                    exit;
                }
            }
        }
        return self::$instance;
    }

    // Prevent instantiation
    private function __construct()
    {
    }
    private function __clone()
    {
    }
}
