<?php
require_once 'config.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New password and confirm password do not match.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'Password must have at least 6 characters.';
    } else {
        try {
            $pdo = getDBConnection();
            $sql = "SELECT password FROM users WHERE id = :id";

            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":id", $_SESSION["user_id"], PDO::PARAM_INT);

                if ($stmt->execute()) {
                    if ($row = $stmt->fetch()) {
                        $hashed_password = $row['password'];
                        if (password_verify($current_password, $hashed_password)) {
                            // Current password is correct, update to new password
                            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $update_sql = "UPDATE users SET password = :password WHERE id = :id";

                            if ($update_stmt = $pdo->prepare($update_sql)) {
                                $update_stmt->bindParam(":password", $new_hashed_password, PDO::PARAM_STR);
                                $update_stmt->bindParam(":id", $_SESSION["user_id"], PDO::PARAM_INT);

                                if ($update_stmt->execute()) {
                                    $success_message = 'Password updated successfully.';
                                } else {
                                    $error_message = 'Oops! Something went wrong. Please try again later.';
                                }
                                unset($update_stmt);
                            }
                        } else {
                            $error_message = 'The current password you entered is not valid.';
                        }
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
    <title>Change Password - CineCraze Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e50914;
            --background: #141414;
            --surface: #1f1f1f;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --danger: #f40612;
            --success: #46d369;
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
        .container {
            background-color: var(--surface);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 450px;
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
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #333;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: var(--text);
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="password"]:focus {
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
        .message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-message {
            color: var(--danger);
            background-color: rgba(244, 6, 18, 0.1);
            border: 1px solid var(--danger);
        }
        .success-message {
            color: var(--success);
            background-color: rgba(70, 211, 105, 0.1);
            border: 1px solid var(--success);
        }
        .nav-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--text-secondary);
            text-decoration: none;
        }
        .nav-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Password</h1>
        <?php
        if(!empty($error_message)){
            echo '<div class="message error-message">' . htmlspecialchars($error_message) . '</div>';
        }
        if(!empty($success_message)){
            echo '<div class="message success-message">' . htmlspecialchars($success_message) . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Update Password</button>
            </div>
        </form>
        <a href="admin.php" class="nav-link">Back to Admin Panel</a>
    </div>
</body>
</html>