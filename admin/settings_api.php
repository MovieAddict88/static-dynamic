<?php
session_start();
header('Content-Type: application/json');

// Check for admin authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

// A simple function to get and lock the settings row
function getServers($pdo) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_embed_servers' FOR UPDATE");
    $stmt->execute();
    $result = $stmt->fetchColumn();
    if ($result === false) {
        // This case should ideally not happen if install.php ran correctly
        return [];
    }
    return json_decode($result, true);
}

// A simple function to save the settings row
function saveServers($pdo, $servers) {
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'auto_embed_servers'");
    return $stmt->execute([json_encode(array_values($servers))]); // Re-index array
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        if ($action === 'add_server') {
            $url = trim($_POST['url'] ?? '');
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid or empty URL provided.');
            }

            $servers = getServers($pdo);

            if (in_array($url, $servers)) {
                throw new Exception('This server URL already exists.');
            }

            $servers[] = $url;

            if (saveServers($pdo, $servers)) {
                $response = ['success' => true, 'message' => 'Server added successfully.'];
            } else {
                throw new Exception('Failed to save the new server list.');
            }
        } elseif ($action === 'delete_server') {
            $url = trim($_POST['url'] ?? '');
            if (empty($url)) {
                throw new Exception('No URL provided for deletion.');
            }

            $servers = getServers($pdo);
            $initial_count = count($servers);

            $servers = array_filter($servers, function($server) use ($url) {
                return $server !== $url;
            });

            if (count($servers) === $initial_count) {
                 throw new Exception('Server URL not found in the list.');
            }

            if (saveServers($pdo, $servers)) {
                $response = ['success' => true, 'message' => 'Server deleted successfully.'];
            } else {
                throw new Exception('Failed to save the updated server list.');
            }
        }

        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
}

echo json_encode($response);
