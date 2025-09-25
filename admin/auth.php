<?php
session_start();

// This script should be included by other admin pages.
// It checks if the user is logged in and if the config file exists.

// Check if config file exists. If not, redirect to install.
if (!file_exists(__DIR__ . '/../includes/config.php')) {
    header('Location: ../install.php');
    exit;
}

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>