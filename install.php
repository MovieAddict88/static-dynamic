<?php
// Simple installer script for CineCraze
// This script should be deleted after running it successfully.

// --- Database Configuration ---
// Note: In a real application, this would be in a separate, non-public file.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'cinecraze';

// --- Establish Connection ---
try {
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// --- Create Database and Select It ---
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$db_name`;");
    echo "Database '$db_name' created or already exists. Now creating tables...<br>";
} catch (PDOException $e) {
    die("Database creation failed: " . $e->getMessage());
}

// --- SQL Statements for Table Creation ---
$sql = "
-- Users Table (for admin)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Genres Table
CREATE TABLE IF NOT EXISTS `genres` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Content Table (for movies, series, live TV)
CREATE TABLE IF NOT EXISTS `content` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tmdb_id` INT UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `poster_url` VARCHAR(255),
    `thumbnail_url` VARCHAR(255),
    `content_type` ENUM('movie', 'series', 'live') NOT NULL,
    `release_date` DATE,
    `rating` DECIMAL(3,1),
    `duration_minutes` INT,
    `parental_rating` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_content_type` (`content_type`),
    INDEX `idx_release_date` (`release_date`),
    INDEX `idx_rating` (`rating`),
    INDEX `idx_title` (`title`)
) ENGINE=InnoDB;

-- Content-Genres Pivot Table
CREATE TABLE IF NOT EXISTS `content_genres` (
    `content_id` INT NOT NULL,
    `genre_id` INT NOT NULL,
    PRIMARY KEY (`content_id`, `genre_id`),
    FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`genre_id`) REFERENCES `genres`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seasons Table (for TV series)
CREATE TABLE IF NOT EXISTS `seasons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `content_id` INT NOT NULL,
    `season_number` INT NOT NULL,
    `poster_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_season` (`content_id`, `season_number`),
    FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Episodes Table (for TV series)
CREATE TABLE IF NOT EXISTS `episodes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `season_id` INT NOT NULL,
    `episode_number` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `thumbnail_url` VARCHAR(255),
    `duration_minutes` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_episode` (`season_id`, `episode_number`),
    FOREIGN KEY (`season_id`) REFERENCES `seasons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Servers Table (for video sources)
CREATE TABLE IF NOT EXISTS `servers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `content_id` INT,
    `episode_id` INT,
    `name` VARCHAR(255) NOT NULL,
    `url` TEXT NOT NULL,
    `quality` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`episode_id`) REFERENCES `episodes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// --- Execute SQL and Insert Default Admin User ---
try {
    $pdo->exec($sql);
    echo "Tables created successfully.<br>";

    // Insert a default admin user if one doesn't exist
    $stmt = $pdo->prepare("SELECT id FROM `users` WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $username = 'admin';
        $password = 'password'; // Default password, user should change this
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $pdo->prepare("INSERT INTO `users` (username, password) VALUES (:username, :password)");
        $insertStmt->execute(['username' => $username, 'password' => $hashed_password]);
        echo "Default admin user created.<br>";
        echo "<b>Username:</b> admin<br>";
        echo "<b>Password:</b> password<br>";
        echo "<strong>IMPORTANT:</strong> Please change this password immediately after logging in.<br>";
    } else {
        echo "Admin user already exists.<br>";
    }

    echo "<h2>Installation Complete!</h2>";
    echo "<p style='color:red; font-weight:bold;'>Please delete this 'install.php' file from your server now for security reasons.</p>";

} catch (PDOException $e) {
    die("Table creation or user insertion failed: " . $e->getMessage());
}

?>