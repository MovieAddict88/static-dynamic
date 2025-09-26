<?php
require_once 'config.php';

// If user is already logged in, redirect to admin panel
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: admin.php");
    exit;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        $error_message = 'Please enter both username and password.';
    } else {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        try {
            $pdo = getDBConnection();
            $sql = "SELECT id, username, password FROM users WHERE username = :username";

            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":username", $username, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 1) {
                        if ($row = $stmt->fetch()) {
                            $id = $row["id"];
                            $hashed_password = $row["password"];
                            if (password_verify($password, $hashed_password)) {
                                // Password is correct, so start a new session
                                session_regenerate_id();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["user_id"] = $id;
                                $_SESSION["username"] = $username;

                                // Redirect to admin panel
                                header("location: admin.php");
                                exit;
                            } else {
                                $error_message = 'Invalid username or password.';
                            }
                        }
                    } else {
                        $error_message = 'Invalid username or password.';
                    }
                } else {
                    $error_message = 'Oops! Something went wrong. Please try again later.';
                }
                unset($stmt);
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
        unset($pdo);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CineCraze</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e50914;
            --background: #141414;
            --surface: #1f1f1f;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --danger: #f40612;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: var(--surface);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            color: var(--primary);
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 600;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #333;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: var(--text);
            font-size: 16px;
            box-sizing: border-box; /* Important for padding */
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background-color: var(--primary);
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #b8070f;
        }
        .error-message {
            color: var(--danger);
            background-color: rgba(244, 6, 18, 0.1);
            border: 1px solid var(--danger);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Panel Login</h1>
        <?php
        if(!empty($error_message)){
            echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Login</button>
            </div>
        </form>
    </div>
</body>
</html>