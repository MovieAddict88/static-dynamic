<?php
require_once 'check_login.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid content ID.']);
        exit;
    }

    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$link) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    $stmt = mysqli_prepare($link, "DELETE FROM content WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Content deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete content.']);
    }

    mysqli_close($link);
}
?>