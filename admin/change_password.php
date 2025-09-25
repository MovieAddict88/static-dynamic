<?php
require_once 'auth.php';
require_once __DIR__ . '/../includes/config.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        // Get the current user's hashed password from the database
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify the old password
        if (password_verify($old_password, $user['password'])) {
            // Hash the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hashed_password, $user_id);
            if ($update_stmt->execute()) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error updating password. Please try again.";
            }
            $update_stmt->close();
        } else {
            $error_message = "Incorrect old password.";
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - CineCraze Admin</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #141414; color: #fff; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 40px auto; background-color: #1f1f1f; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        h1 { color: #e50914; text-align: center; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .error { background-color: #e87c03; color: #fff; }
        .success { background-color: #4CAF50; color: #fff; }
        form input { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #333; background-color: #333; color: #fff; font-size: 16px; box-sizing: border-box; }
        form button { width: 100%; padding: 12px; border: none; border-radius: 4px; background-color: #e50914; color: #fff; font-size: 16px; font-weight: bold; cursor: pointer; }
        .nav-link { display: block; text-align: center; margin-top: 20px; color: #8c8c8c; text-decoration: none; }
        .nav-link:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Password</h1>
        <?php if ($error_message): ?>
            <p class="message error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p class="message success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <form method="POST" action="change_password.php">
            <input type="password" name="old_password" placeholder="Old Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit">Change Password</button>
        </form>
        <a href="dashboard.php" class="nav-link">Back to Dashboard</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </div>
</body>
</html>