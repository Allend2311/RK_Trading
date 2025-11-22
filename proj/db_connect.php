<?php
// Database connection for rk_trading_db1
// Update credentials as needed for your local XAMPP MySQL
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'rk_trading1';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Ensure users table exists with needed fields
$mysqli->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    picture VARCHAR(512) DEFAULT NULL,
    password VARCHAR(255) NULL, -- Added for local logins
    user_type VARCHAR(50) NOT NULL DEFAULT 'customer', -- Added for user roles
    provider VARCHAR(50) DEFAULT NULL,
    provider_id VARCHAR(191) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (provider),
    INDEX (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

?>