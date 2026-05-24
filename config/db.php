<?php
session_start();

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_PORT = getenv('DB_PORT') ?: '3306';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$DB_NAME = getenv('DB_NAME') ?: 'sleep_tracker';

try {
    $bootstrap = new PDO(
        "mysql:host={$DB_HOST};port={$DB_PORT};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $bootstrap->exec("CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $conn = new PDO(
        "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(
        "Database connection failed: " . htmlspecialchars($e->getMessage()) .
        "<br><br>Make sure Apache + MySQL are running in XAMPP, then refresh."
    );
}

$conn->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->exec("CREATE TABLE IF NOT EXISTS sleep_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sleep_time DATETIME NOT NULL,
    wakeup_time DATETIME NOT NULL,
    sleep_quality ENUM('Excellent','Good','Fair','Poor') NOT NULL,
    notes TEXT,
    duration DECIMAL(5,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, sleep_time),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
