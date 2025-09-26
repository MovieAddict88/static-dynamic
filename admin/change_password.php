<?php
require_once '../config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $response['message'] = 'All fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $response['message'] = 'New password and confirmation do not match.';
    } elseif (strlen($newPassword) < 6) {
        $response['message'] = 'New password must be at least 6 characters long.';
    } else {
        try {
            $conn = getDBConnection();

            // --- Verify Current Password ---
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($currentPassword, $user['password'])) {
                // --- Current password is correct, update to new password ---
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                $updateStmt->bindParam(':password', $newPasswordHash);
                $updateStmt->bindParam(':id', $userId);

                if ($updateStmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Password changed successfully!';
                } else {
                    $response['message'] = 'Failed to update password in the database.';
                }
            } else {
                $response['message'] = 'Incorrect current password.';
            }
        } catch (PDOException $e) {
            // In a production environment, you would log this error instead of displaying it.
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>