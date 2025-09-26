<?php
// Database Configuration
define('DB_HOST', 'YOUR_DATABASE_HOST'); // e.g., 'sql123.infinityfree.com'
define('DB_NAME', 'YOUR_DATABASE_NAME'); // e.g., 'if0_12345678_cinecraze'
define('DB_USER', 'YOUR_DATABASE_USER'); // e.g., 'if0_12345678'
define('DB_PASS', 'YOUR_DATABASE_PASSWORD');

// TMDB API Key
// You can get a free API key from https://www.themoviedb.org/
define('TMDB_API_KEY', 'ec926176bf467b3f7735e3154238c161');

// Start the session
// This is needed for admin authentication
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting
// Set to 0 for production environments
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

/**
 * A simple helper function to connect to the database.
 * Returns a PDO object.
 */
function getDBConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO.ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}