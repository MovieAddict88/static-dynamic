<?php
// CineCraze Installer
// This script will guide you through the setup process.

error_reporting(E_ALL);
ini_set('display_errors', 1);

$configFile = 'config.php';
$schemaFile = 'includes/schema.sql';
$errors = [];
$success = '';

// Check if config file already exists
if (file_exists($configFile)) {
    die("
        <style>
            body { font-family: sans-serif; background-color: #141414; color: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .container { text-align: center; padding: 40px; background-color: #1a1a1a; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); border: 1px solid #333; }
            h1 { color: #e50914; }
            p { font-size: 1.1em; }
            a { color: #e50914; text-decoration: none; font-weight: bold; }
        </style>
        <div class='container'>
            <h1>Already Installed</h1>
            <p>The configuration file (<code>config.php</code>) already exists. To prevent accidental reconfiguration, the installer has been disabled.</p>
            <p>If you need to reinstall, please delete the <code>config.php</code> file and run this installer again.</p>
            <p><a href='./'>Go to Homepage</a> or <a href='./admin/'>Go to Admin Panel</a></p>
        </div>
    ");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_user = $_POST['admin_user'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';
    $tmdb_api_key = $_POST['tmdb_api_key'] ?? '';

    // Validation
    if (empty($db_host)) $errors[] = "Database Host is required.";
    if (empty($db_name)) $errors[] = "Database Name is required.";
    if (empty($db_user)) $errors[] = "Database User is required.";
    if (empty($admin_user)) $errors[] = "Admin Username is required.";
    if (empty($admin_pass)) $errors[] = "Admin Password is required.";
    if (empty($tmdb_api_key)) $errors[] = "TMDB API Key is required.";
    if (!file_exists($schemaFile)) $errors[] = "Schema file (<code>$schemaFile</code>) not found!";

    if (empty($errors)) {
        // --- Step 1: Create config.php ---
        $configContent = "<?php\n\n";
        $configContent .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
        $configContent .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
        $configContent .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
        $configContent .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
        $configContent .= "define('TMDB_API_KEY', '" . addslashes($tmdb_api_key) . "');\n";


        if (@file_put_contents($configFile, $configContent) === false) {
            $errors[] = "Could not write to <code>config.php</code>. Please check file permissions.";
        } else {
            // --- Step 2: Connect to MySQL and create database ---
            try {
                $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$db_name`");

                // --- Step 3: Import schema ---
                $sql = file_get_contents($schemaFile);
                $pdo->exec($sql);

                // --- Step 4: Create admin user ---
                $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
                $stmt->execute([$admin_user, $hashed_password]);

                $success = "Installation complete! CineCraze is ready to go.";

                // --- Step 5: Self-destruct installer ---
                if (isset($_POST['delete_installer'])) {
                    unlink(__FILE__);
                    $success .= " This installer file has been deleted for security.";
                }

            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
                // Clean up config file on error
                if (file_exists($configFile)) {
                    unlink($configFile);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineCraze Installer</title>
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b8070f;
            --background: #141414;
            --surface: #1a1a1a;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --success: #46d369;
            --danger: #f40612;
            --border-radius: 12px;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        .installer-container {
            width: 100%;
            max-width: 600px;
            background-color: var(--surface);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid #333;
        }
        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-secondary);
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 6px;
            background-color: #333;
            color: var(--text);
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.25);
        }
        .btn {
            width: 100%;
            padding: 15px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: var(--primary-dark);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid;
        }
        .alert-danger {
            background-color: rgba(244, 6, 18, 0.1);
            color: var(--danger);
            border-color: rgba(244, 6, 18, 0.3);
        }
        .alert-success {
            background-color: rgba(70, 211, 105, 0.1);
            color: var(--success);
            border-color: rgba(70, 211, 105, 0.3);
            text-align: center;
        }
        .alert-success a {
            color: var(--success);
            font-weight: bold;
            text-decoration: underline;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
        .checkbox-group input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <h1>CineCraze Installation</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <p><?php echo $success; ?></p>
                <p><a href="./admin/">Go to Admin Panel</a></p>
            </div>
        <?php else: ?>
            <p class="subtitle">Enter your database and admin details to get started.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Oops! Something went wrong.</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="install.php">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" required>
                </div>
                <div class="form-group">
                    <label for="db_user">Database User</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>
                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                <hr style="border-color: #333; margin: 30px 0;">
                <div class="form-group">
                    <label for="admin_user">Admin Username</label>
                    <input type="text" id="admin_user" name="admin_user" required>
                </div>
                <div class="form-group">
                    <label for="admin_pass">Admin Password</label>
                    <input type="password" id="admin_pass" name="admin_pass" required>
                </div>
                <hr style="border-color: #333; margin: 30px 0;">
                <div class="form-group">
                    <label for="tmdb_api_key">TMDB API Key</label>
                    <input type="text" id="tmdb_api_key" name="tmdb_api_key" required>
                    <small style="color: #888; font-size: 12px; display: block; margin-top: 5px;">Required for generating content from TMDB.</small>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="delete_installer" name="delete_installer" checked>
                    <label for="delete_installer">Delete this installer file after setup (Recommended)</label>
                </div>
                <button type="submit" class="btn">Install Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>