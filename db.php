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
        $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
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
