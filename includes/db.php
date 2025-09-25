<?php
// Database connection file
// This file is included in other files to connect to the database.

// The config file is created by install.php
if (!file_exists(__DIR__ . '/../config.php')) {
    // If config doesn't exist, redirect to installer
    header('Location: ../install.php');
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In a real application, you would log this error and show a generic message.
    // For development, it's useful to see the error.
    die("Database connection failed: " . $e->getMessage());
}
?>