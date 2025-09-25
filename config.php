<?php
// Database configuration - these values will be replaced by the installer.
define('DB_HOST', '{{DB_HOST}}');
define('DB_USERNAME', '{{DB_USERNAME}}');
define('DB_PASSWORD', '{{DB_PASSWORD}}');
define('DB_NAME', '{{DB_NAME}}');

/**
 * Connect to the database.
 * This will be included in other files.
 * The check for '{{DB_HOST}}' prevents connection errors during the installation process.
 */
function connect_db() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If the config file hasn't been configured yet, don't throw a fatal error.
        // The installer will handle this.
        if (DB_HOST === '{{DB_HOST}}') {
            return null;
        }
        // For a configured system, it's a fatal error.
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}

// You can now include this file and call connect_db() to get a PDO instance.
// Example:
// require_once 'config.php';
// $pdo = connect_db();
// if ($pdo) { ... }
?>
