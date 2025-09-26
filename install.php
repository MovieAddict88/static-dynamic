<?php
// Simple installer for CineCraze
// This script will create the necessary database tables.

// --- IMPORTANT ---
// 1. CREATE A DATABASE in your hosting panel (e.g., cPanel, InfinityFree Vistapanel).
// 2. CREATE A DATABASE USER and assign it to the database with ALL PRIVILEGES.
// 3. UPDATE the config.php file with your database host, name, username, and password.
// 4. UPLOAD all files to your server.
// 5. NAVIGATE to this install.php file in your browser to run the installer.
// 6. DELETE this file after installation is complete for security.

// Include the configuration file
require_once 'config.php';

// --- Installation Logic ---
$message = "";

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- Step 1: Create Database if it doesn't exist ---
    $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $message .= "<p>✅ Database '" . DB_NAME . "' checked/created successfully.</p>";

    // --- Step 2: Select the database ---
    $conn->exec("USE `" . DB_NAME . "`");
    $message .= "<p>✅ Selected database '" . DB_NAME . "'.</p>";

    // --- Step 3: Create Tables ---
    $sql_commands = [
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;",

        "CREATE TABLE IF NOT EXISTS `content` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tmdb_id` INT NULL,
            `type` ENUM('movie', 'series', 'live') NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `poster_path` VARCHAR(255),
            `backdrop_path` VARCHAR(255),
            `release_date` DATE,
            `year` YEAR,
            `runtime` INT,
            `rating` DECIMAL(3,1),
            `parental_rating` VARCHAR(20),
            `genres` TEXT,
            `trailer_url` VARCHAR(255),
            `country` VARCHAR(100),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `tmdb_id_type_unique` (`tmdb_id`, `type`),
            INDEX `type_idx` (`type`),
            INDEX `year_idx` (`year`),
            INDEX `rating_idx` (`rating`),
            FULLTEXT KEY `title_search_idx` (`title`)
        ) ENGINE=InnoDB;",

        "CREATE TABLE IF NOT EXISTS `seasons` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `content_id` INT NOT NULL,
            `season_number` INT NOT NULL,
            `name` VARCHAR(255),
            `poster_path` VARCHAR(255),
            INDEX `content_id_idx` (`content_id`),
            FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB;",

        "CREATE TABLE IF NOT EXISTS `episodes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `season_id` INT NOT NULL,
            `episode_number` INT NOT NULL,
            `title` VARCHAR(255),
            `description` TEXT,
            `still_path` VARCHAR(255),
            `release_date` DATE,
            INDEX `season_id_idx` (`season_id`),
            FOREIGN KEY (`season_id`) REFERENCES `seasons`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB;",

        "CREATE TABLE IF NOT EXISTS `servers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `content_id` INT NULL,
            `episode_id` INT NULL,
            `name` VARCHAR(255) NOT NULL,
            `url` TEXT NOT NULL,
            `quality` VARCHAR(20),
            INDEX `content_id_idx` (`content_id`),
            INDEX `episode_id_idx` (`episode_id`),
            FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`episode_id`) REFERENCES `episodes`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB;"
    ];

    foreach ($sql_commands as $command) {
        $conn->exec($command);
    }
    $message .= "<p>✅ All tables created successfully.</p>";

    // --- Step 4: Add Default Admin User ---
    // Check if admin user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $admin_user = 'admin';
        // IMPORTANT: Use a more secure password in a real application
        $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $admin_user);
        $stmt->bindParam(':password', $admin_pass);
        $stmt->execute();
        $message .= "<p>✅ Default admin user created.</p>";
        $message .= "<p><strong>Username:</strong> admin</p>";
        $message .= "<p><strong>Password:</strong> admin123</p>";
    } else {
        $message .= "<p>ℹ️ Admin user already exists. Skipping creation.</p>";
    }

    $message .= "<h2>🎉 Installation Complete!</h2>";
    $message .= "<p style='color:red; font-weight:bold;'>For security reasons, please delete this `install.php` file from your server now.</p>";

} catch(PDOException $e) {
    $message = "<h2>❌ Installation Failed!</h2>";
    $message .= "<p>An error occurred: " . $e->getMessage() . "</p>";
    $message .= "<p>Please check your `config.php` settings and ensure your database user has the correct privileges.</p>";
}

$conn = null; // Close connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineCraze Installer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #141414; color: #fff; margin: 0; padding: 40px; text-align: center; }
        .installer-box { background-color: #1f1f1f; border: 1px solid #333; border-radius: 12px; max-width: 700px; margin: auto; padding: 30px; text-align: left; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #e50914; }
        p { line-height: 1.6; color: #e6e6e6; }
        strong { color: #fff; }
        h2 { color: #46d369; }
        code { background-color: #333; padding: 2px 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="installer-box">
        <h1>🎬 CineCraze Installer</h1>
        <hr style="border: 1px solid #333; margin: 20px 0;">
        <div>
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>