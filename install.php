<?php
// CineCraze Installation Script

// --- Configuration & Helpers ---

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // 5 minutes

session_start();

function get_step() {
    return isset($_SESSION['step']) ? $_SESSION['step'] : 1;
}

function set_step($step) {
    $_SESSION['step'] = $step;
}

function get_config_data() {
    return isset($_SESSION['config_data']) ? $_SESSION['config_data'] : [];
}

function set_config_data($data) {
    $_SESSION['config_data'] = $data;
}

function add_message($type, $message) {
    if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = [];
    }
    $_SESSION['messages'][] = ['type' => $type, 'text' => $message];
}

function display_messages() {
    if (isset($_SESSION['messages'])) {
        foreach ($_SESSION['messages'] as $msg) {
            echo '<div class="status ' . htmlspecialchars($msg['type']) . '">' . htmlspecialchars($msg['text']) . '</div>';
        }
        unset($_SESSION['messages']);
    }
}

// --- Logic ---

$current_step = get_step();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset') {
        session_destroy();
        header('Location: install.php');
        exit;
    }

    if ($current_step === 1 && $action === 'setup_db') {
        $db_host = $_POST['db_host'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';
        $db_name = $_POST['db_name'] ?? '';

        if (empty($db_host) || empty($db_user) || empty($db_name)) {
            add_message('error', 'Database host, username, and name are required.');
        } else {
            try {
                // 1. Test connection without selecting DB
                $conn = new mysqli($db_host, $db_user, $db_pass);
                if ($conn->connect_error) {
                    throw new Exception("Connection failed: " . $conn->connect_error);
                }
                add_message('success', 'Database connection successful.');

                // 2. Create database if it doesn't exist
                $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name`");
                if ($conn->error) {
                    throw new Exception("Database creation failed: " . $conn->error);
                }
                add_message('success', "Database '$db_name' is ready.");

                // 3. Select the database
                $conn->select_db($db_name);
                if ($conn->error) {
                    throw new Exception("Failed to select database: " . $conn->error);
                }

                // 4. Create tables
                $sql_queries = [
                    "CREATE TABLE IF NOT EXISTS `users` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `username` VARCHAR(50) NOT NULL UNIQUE,
                        `password` VARCHAR(255) NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                    "CREATE TABLE IF NOT EXISTS `content` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `tmdb_id` INT NULL UNIQUE,
                        `title` VARCHAR(255) NOT NULL,
                        `description` TEXT,
                        `poster` VARCHAR(255),
                        `thumbnail` VARCHAR(255),
                        `type` ENUM('movie', 'series', 'live') NOT NULL,
                        `release_year` INT,
                        `rating` DECIMAL(3,1),
                        `parental_rating` VARCHAR(50),
                        `duration` VARCHAR(50),
                        `country` VARCHAR(100),
                        `subcategory` VARCHAR(100)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                    "CREATE TABLE IF NOT EXISTS `seasons` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `content_id` INT NOT NULL,
                        `season_number` INT NOT NULL,
                        `poster` VARCHAR(255),
                        FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                    "CREATE TABLE IF NOT EXISTS `episodes` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `season_id` INT NOT NULL,
                        `episode_number` INT NOT NULL,
                        `title` VARCHAR(255),
                        `description` TEXT,
                        `thumbnail` VARCHAR(255),
                        `duration` VARCHAR(50),
                        FOREIGN KEY (`season_id`) REFERENCES `seasons`(`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                    "CREATE TABLE IF NOT EXISTS `servers` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `content_id` INT NULL,
                        `episode_id` INT NULL,
                        `name` VARCHAR(255) NOT NULL,
                        `url` VARCHAR(512) NOT NULL,
                        `quality` VARCHAR(50),
                        FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE,
                        FOREIGN KEY (`episode_id`) REFERENCES `episodes`(`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
                ];

                foreach ($sql_queries as $query) {
                    if (!$conn->query($query)) {
                        throw new Exception("Table creation failed: " . $conn->error);
                    }
                }
                add_message('success', 'All database tables created successfully.');

                $conn->close();

                set_config_data([
                    'db_host' => $db_host,
                    'db_user' => $db_user,
                    'db_pass' => $db_pass,
                    'db_name' => $db_name
                ]);
                set_step(2);

            } catch (Exception $e) {
                add_message('error', $e->getMessage());
            }
        }
    } elseif ($current_step === 2 && $action === 'create_admin') {
        $admin_user = $_POST['admin_user'] ?? '';
        $admin_pass = $_POST['admin_pass'] ?? '';
        $admin_pass_confirm = $_POST['admin_pass_confirm'] ?? '';

        if (empty($admin_user) || empty($admin_pass)) {
            add_message('error', 'Admin username and password are required.');
        } elseif ($admin_pass !== $admin_pass_confirm) {
            add_message('error', 'Passwords do not match.');
        } else {
            $config = get_config_data();
            try {
                $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
                if ($conn->connect_error) {
                    throw new Exception("Database connection failed: " . $conn->connect_error);
                }

                $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO `users` (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $admin_user, $hashed_password);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to create admin user: " . $stmt->error);
                }

                $stmt->close();
                $conn->close();
                add_message('success', 'Admin user created successfully.');
                set_step(3);

            } catch (Exception $e) {
                add_message('error', $e->getMessage());
            }
        }
    } elseif ($current_step === 3 && $action === 'finish_install') {
        $config = get_config_data();
        $config_content = "<?php
// CineCraze Configuration File
// Generated by installation script

define('DB_HOST', '" . addslashes($config['db_host']) . "');
define('DB_USER', '" . addslashes($config['db_user']) . "');
define('DB_PASS', '" . addslashes($config['db_pass']) . "');
define('DB_NAME', '" . addslashes($config['db_name']) . "');

// Establish database connection
\$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (\$conn->connect_error) {
    die('Connection Failed: ' . \$conn->connect_error);
}

// Set charset
\$conn->set_charset('utf8mb4');
?>";

        try {
            $config_path = __DIR__ . '/includes/config.php';
            if (file_put_contents($config_path, $config_content) === false) {
                throw new Exception("Could not write to config file. Please check permissions for the 'includes' directory.");
            }
            add_message('success', 'Configuration file created successfully.');
            set_step(4);
            session_destroy();
        } catch (Exception $e) {
            add_message('error', $e->getMessage());
            add_message('info', 'Please manually create the file includes/config.php with the following content: <br><pre>' . htmlspecialchars($config_content) . '</pre>');
        }
    }
    header('Location: install.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineCraze Installation</title>
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b8070f;
            --background: #141414;
            --surface: #1f1f1f;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --success: #46d369;
            --warning: #ffa500;
            --danger: #f40612;
            --info: #00d4ff;
            --border-radius: 8px;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--background);
            color: var(--text);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background-color: var(--surface);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        h1 {
            color: var(--primary);
            text-align: center;
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
            border: 1px solid #333;
            border-radius: var(--border-radius);
            background-color: #101010;
            color: var(--text);
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: var(--border-radius);
            background-color: var(--primary);
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: var(--primary-dark);
        }
        .status {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        .status.success { background-color: rgba(70, 211, 105, 0.1); border-color: var(--success); color: var(--success); }
        .status.error { background-color: rgba(244, 6, 18, 0.1); border-color: var(--danger); color: var(--danger); }
        .status.info { background-color: rgba(0, 212, 255, 0.1); border-color: var(--info); color: var(--info); }
        .reset-form {
            text-align: center;
            margin-top: 20px;
        }
        .reset-form button {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CineCraze Installation</h1>
        <?php display_messages(); ?>

        <?php if ($current_step === 1): ?>
            <h2>Step 1: Database Setup</h2>
            <p>Please provide your MySQL database credentials. The installer will attempt to create the database and tables.</p>
            <form method="POST">
                <input type="hidden" name="action" value="setup_db">
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
                <button type="submit" class="btn">Setup Database</button>
            </form>
        <?php elseif ($current_step === 2): ?>
            <h2>Step 2: Create Admin User</h2>
            <p>The database is ready. Now, create your admin account.</p>
            <form method="POST">
                <input type="hidden" name="action" value="create_admin">
                <div class="form-group">
                    <label for="admin_user">Admin Username</label>
                    <input type="text" id="admin_user" name="admin_user" required>
                </div>
                <div class="form-group">
                    <label for="admin_pass">Admin Password</label>
                    <input type="password" id="admin_pass" name="admin_pass" required>
                </div>
                <div class="form-group">
                    <label for="admin_pass_confirm">Confirm Password</label>
                    <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" required>
                </div>
                <button type="submit" class="btn">Create Admin</button>
            </form>
        <?php elseif ($current_step === 3): ?>
            <h2>Step 3: Finalize Installation</h2>
            <p>Admin user created. The final step is to create the <code>includes/config.php</code> file.</p>
            <form method="POST">
                <input type="hidden" name="action" value="finish_install">
                <button type="submit" class="btn">Create Config File & Finish</button>
            </form>
        <?php elseif ($current_step === 4): ?>
            <h2>Installation Complete!</h2>
            <div class="status success">
                <strong>Congratulations!</strong> CineCraze has been installed successfully.
            </div>
            <p>For security reasons, please delete the <code>install.php</code> file from your server now.</p>
            <a href="admin/" class="btn">Go to Admin Panel</a>
        <?php endif; ?>

        <?php if ($current_step !== 4): ?>
            <form method="POST" class="reset-form">
                <input type="hidden" name="action" value="reset">
                <button type="submit">Start Over</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>