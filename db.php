<?php
$DB_HOST = 'localhost';          // or your RDS endpoint
$DB_PORT = '3306';
$DB_NAME = 'bookstore';
$DB_USER = 'root';               // or RDS username
$DB_PASS = '';                   // or RDS password

function getPDO() {
    global $DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS;
    static $pdo = null;

    if ($pdo === null) {
        $dsnOverride = getenv('DB_DSN');
        $dsn = $dsnOverride ?: "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
        $user = getenv('DB_USER') ?: $DB_USER;
        $pass = getenv('DB_PASS') ?: $DB_PASS;
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log("DB connection error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection error'
            ]);
            exit;
        }
    }
    return $pdo;
}
