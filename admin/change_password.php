<?php
session_start();
require_once '../config.php';

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        try {
            $pdo = connect_db();
            if ($pdo) {
                // Get current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                if ($user && password_verify($old_password, $user['password'])) {
                    // Old password is correct, update to new password
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hashed_password, $user_id]);
                    $message = "Password changed successfully.";
                } else {
                    $error = "Incorrect old password.";
                }
            } else {
                $error = 'Database connection failed.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - CineCraze Admin</title>
    <style>
        :root {
            --primary: #e50914;
            --background: #141414;
            --surface: #1f1f1f;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --success: #46d369;
            --danger: #f40612;
            --border-radius: 8px;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background);
            color: var(--text);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 40px;
            background-color: var(--surface);
            border-radius: var(--border-radius);
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
            font-weight: 500;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            background-color: var(--background);
            border: 1px solid #333;
            border-radius: var(--border-radius);
            color: var(--text);
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background-color: var(--primary);
            border: none;
            border-radius: var(--border-radius);
            color: var(--text);
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            text-align: center;
        }
        .message.error {
            background-color: rgba(244, 6, 18, 0.2);
            color: var(--danger);
        }
        .message.success {
            background-color: rgba(70, 211, 105, 0.2);
            color: var(--success);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Password</h1>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="change_password.php" method="POST">
            <div class="form-group">
                <label for="old_password">Old Password</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Change Password</button>
        </form>
        <a href="index.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>
