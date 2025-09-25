<?php
require_once 'auth.php'; // Protect this page
require_once __DIR__ . '/../includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        try {
            // Get current user from DB
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            // Verify current password
            if ($admin && password_verify($current_password, $admin['password'])) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in DB
                $update_stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $update_stmt->execute([$hashed_password, $_SESSION['admin_id']]);

                $success = 'Password changed successfully!';
            } else {
                $error = 'Incorrect current password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
            error_log($e->getMessage());
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
            --primary-dark: #b8070f;
            --background: #141414;
            --surface: #1a1a1a;
            --text: #ffffff;
            --danger: #f40612;
            --success: #46d369;
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
        .container {
            width: 100%;
            max-width: 500px;
            padding: 40px;
            background-color: var(--surface);
            border-radius: 12px;
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
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 6px;
            background-color: #333;
            color: var(--text);
            font-size: 16px;
            box-sizing: border-box;
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
            margin-top: 10px;
        }
        .message {
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
        }
        .error {
            color: var(--danger);
            background-color: rgba(244, 6, 18, 0.1);
        }
        .success {
            color: var(--success);
            background-color: rgba(70, 211, 105, 0.1);
        }
        a {
            color: var(--primary);
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Password</h1>
        <?php if ($error): ?>
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="message success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <form method="POST" action="change_password.php">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
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
        <a href="dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>