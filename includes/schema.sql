-- Database schema for CineCraze
-- This will be executed by the installer script.

CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `genres` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `content` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tmdb_id` INT NULL,
  `type` ENUM('movie', 'series', 'live') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `poster` VARCHAR(255),
  `thumbnail` VARCHAR(255),
  `year` INT,
  `duration` VARCHAR(50),
  `rating` DECIMAL(3,1),
  `parental_rating` VARCHAR(50),
  `country` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`type`),
  INDEX (`year`),
  INDEX (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `content_genres` (
  `content_id` INT NOT NULL,
  `genre_id` INT NOT NULL,
  PRIMARY KEY (`content_id`, `genre_id`),
  FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`genre_id`) REFERENCES `genres`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `seasons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `content_id` INT NOT NULL,
  `season_number` INT NOT NULL,
  `name` VARCHAR(255),
  `poster` VARCHAR(255),
  FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `episodes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `season_id` INT NOT NULL,
  `episode_number` INT NOT NULL,
  `title` VARCHAR(255),
  `description` TEXT,
  `thumbnail` VARCHAR(255),
  `duration` VARCHAR(50),
  FOREIGN KEY (`season_id`) REFERENCES `seasons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `servers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `content_id` INT NULL,
  `episode_id` INT NULL,
  `name` VARCHAR(255) NOT NULL,
  `url` TEXT NOT NULL,
  FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`episode_id`) REFERENCES `episodes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;