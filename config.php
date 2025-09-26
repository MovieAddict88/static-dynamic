<?php
// --- Database Configuration ---
define('DB_HOST', 'sql_server_host'); // Replace with your SQL server host (e.g., sqlXXX.infinityfree.com)
define('DB_USER', 'your_username');      // Replace with your database username
define('DB_PASS', 'your_password');      // Replace with your database password
define('DB_NAME', 'your_dbname');        // Replace with your database name

// --- Site Configuration ---
define('SITE_TITLE', 'CineCraze');
define('BASE_URL', 'http://your_domain.com'); // Replace with your website's URL

// --- TMDB API Key ---
// It's recommended to store this securely
define('TMDB_API_KEY', 'ec926176bf467b3f7735e3154238c161'); // Replace with your TMDB API Key if you have a different one

/**
 * Function to establish a database connection.
 * @return PDO
 */
function getDBConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        // In a production environment, you would log this error and show a generic message.
        // For development, it's useful to see the error.
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

// Start a session for all pages that include this config
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>