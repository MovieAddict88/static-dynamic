<?php
require_once 'auth.php';
require_once __DIR__ . '/../includes/config.php';

function redirect_to_manage($status, $message = '') {
    $url = "manage_content.php?status=$status";
    if (!empty($message)) {
        $url .= "&message=" . urlencode($message);
    }
    header("Location: $url");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to_manage('error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        // --- ADD CONTENT ---
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $poster = $_POST['poster'] ?? '';
        $type = $_POST['type'] ?? 'movie';
        $release_year = !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null;

        if (empty($title) || empty($type)) {
            redirect_to_manage('error', 'Title and Type are required.');
        }

        $stmt = $conn->prepare("INSERT INTO content (title, description, poster, type, release_year) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $description, $poster, $type, $release_year);

        if ($stmt->execute()) {
            redirect_to_manage('success', 'Content added successfully.');
        } else {
            redirect_to_manage('error', 'Failed to add content: ' . $stmt->error);
        }
        $stmt->close();
        break;

    case 'edit':
        // --- EDIT CONTENT ---
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $poster = $_POST['poster'] ?? '';
        $type = $_POST['type'] ?? 'movie';
        $release_year = !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null;

        if ($id <= 0 || empty($title) || empty($type)) {
            redirect_to_manage('error', 'Invalid data provided for update.');
        }

        $stmt = $conn->prepare("UPDATE content SET title = ?, description = ?, poster = ?, type = ?, release_year = ? WHERE id = ?");
        $stmt->bind_param("ssssii", $title, $description, $poster, $type, $release_year, $id);

        if ($stmt->execute()) {
            redirect_to_manage('success', 'Content updated successfully.');
        } else {
            redirect_to_manage('error', 'Failed to update content: ' . $stmt->error);
        }
        $stmt->close();
        break;

    case 'delete':
        // --- DELETE CONTENT ---
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            redirect_to_manage('error', 'Invalid content ID.');
        }

        $stmt = $conn->prepare("DELETE FROM content WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            redirect_to_manage('success', 'Content deleted successfully.');
        } else {
            redirect_to_manage('error', 'Failed to delete content: ' . $stmt->error);
        }
        $stmt->close();
        break;

    default:
        redirect_to_manage('error', 'Invalid action specified.');
        break;
}

$conn->close();
?>