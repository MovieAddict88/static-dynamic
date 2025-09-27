<?php
// Simple Installer for CineCraze

// --- Configuration ---
$db_config_file = 'config.php';
$db_schema_file = 'schema.sql';
$default_admin_user = 'admin';

// --- Helper Functions ---

// Function to display messages
function show_message($message, $type = 'info') {
    echo "<div class='message {$type}'>{$message}</div>";
}

// Function to execute a SQL query
function execute_query($link, $query) {
    if (mysqli_query($link, $query)) {
        return true;
    } else {
        show_message("Error executing query: " . mysqli_error($link), 'error');
        return false;
    }
}

// Function to generate a random password
function generate_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    return substr(str_shuffle($chars), 0, $length);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CineCraze Installer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f4f4; color: #333; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e50914; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid; }
        .info { background-color: #e6f7ff; border-color: #91d5ff; color: #0050b3; }
        .success { background-color: #f6ffed; border-color: #b7eb8f; color: #389e0d; }
        .error { background-color: #fff1f0; border-color: #ffa39e; color: #cf1322; }
        .warning { background-color: #fffbe6; border-color: #ffe58f; color: #d46b08; }
        code { background: #eee; padding: 2px 5px; border-radius: 3px; }
        .credentials { border: 2px dashed #e50914; padding: 20px; text-align: center; margin-top: 20px; }
        .credentials h3 { margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>CineCraze Installer</h1>

        <?php
        // --- Step 1: Check for config.php ---
        if (!file_exists($db_config_file)) {
            if (file_exists('config.php.example')) {
                show_message("The <code>config.php</code> file was not found. Please rename <code>config.php.example</code> to <code>config.php</code> and fill in your database credentials.", 'error');
            } else {
                show_message("The <code>config.php</code> file was not found, and the example file is also missing. Please create a <code>config.php</code> file with your database credentials.", 'error');
            }
            exit;
        }

        show_message("<code>config.php</code> found. Proceeding with installation...", 'success');

        // --- Step 2: Connect to the database ---
        require_once($db_config_file);

        $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);

        if (!$link) {
            show_message("Database connection failed: " . mysqli_connect_error(), 'error');
            exit;
        }

        show_message("Successfully connected to MySQL server.", 'success');

        // --- Step 3: Create the database if it doesn't exist ---
        $db_name = DB_NAME;
        $sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";

        if (execute_query($link, $sql)) {
            show_message("Database <code>$db_name</code> created or already exists.", 'success');
        } else {
            exit;
        }

        // Select the database
        mysqli_select_db($link, $db_name);

        // --- Step 4: Create tables from schema.sql ---
        if (!file_exists($db_schema_file)) {
            show_message("The <code>$db_schema_file</code> file was not found. Cannot create tables.", 'error');
            exit;
        }

        $schema = file_get_contents($db_schema_file);

        if (mysqli_multi_query($link, $schema)) {
            // Clear multi_query results
            while (mysqli_more_results($link) && mysqli_next_result($link)) {;}
            show_message("Database tables created successfully.", 'success');
        } else {
            show_message("Error creating tables: " . mysqli_error($link), 'error');
            exit;
        }

        // --- Step 5: Create a default admin user ---
        $admin_password = generate_password();
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($link, "INSERT INTO users (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password=?");
        mysqli_stmt_bind_param($stmt, "sss", $default_admin_user, $hashed_password, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            show_message("Admin user created/updated successfully.", 'success');
        } else {
            show_message("Error creating admin user: " . mysqli_stmt_error($stmt), 'error');
            exit;
        }

        mysqli_stmt_close($stmt);
        mysqli_close($link);

        // --- Step 6: Display success message ---
        ?>
        <div class="credentials">
            <h3>Installation Complete!</h3>
            <p>Your admin credentials are:</p>
            <p><strong>Username:</strong> <code><?php echo $default_admin_user; ?></code></p>
            <p><strong>Password:</strong> <code><?php echo $admin_password; ?></code></p>
            <p>Please save these credentials in a safe place.</p>
        </div>

        <div class="message warning">
            <strong>IMPORTANT:</strong> For security reasons, please delete this <code>install.php</code> file from your server immediately.
        </div>

        <a href="login.php" style="display: block; text-align: center; font-size: 1.2em; color: #e50914; font-weight: bold;">Go to Admin Login</a>
    </div>
</body>
</html>