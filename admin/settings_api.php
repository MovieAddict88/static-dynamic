<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config.php';

$pdo = connect_db();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please check your configuration.']);
    exit;
}

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

function getServers($pdo) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_embed_servers' FOR UPDATE");
    $stmt->execute();
    $result = $stmt->fetchColumn();
    if ($result === false) return [];
    return json_decode($result, true);
}

function saveServers($pdo, $servers) {
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'auto_embed_servers'");
    return $stmt->execute([json_encode(array_values($servers))]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $servers = getServers($pdo);

        switch ($action) {
            case 'add_server':
                $url = trim($_POST['url'] ?? '');
                if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                    throw new Exception('Invalid or empty URL provided.');
                }
                // Check for duplicates
                foreach ($servers as $server) {
                    if ($server['url'] === $url) {
                        throw new Exception('This server URL already exists.');
                    }
                }
                $servers[] = ['url' => $url, 'enabled' => true];
                $message = 'Server added successfully.';
                break;

            case 'delete_server':
                $url = trim($_POST['url'] ?? '');
                if (empty($url)) {
                    throw new Exception('No URL provided for deletion.');
                }
                $initial_count = count($servers);
                $servers = array_filter($servers, function($server) use ($url) {
                    return $server['url'] !== $url;
                });
                if (count($servers) === $initial_count) {
                    throw new Exception('Server URL not found in the list.');
                }
                $message = 'Server deleted successfully.';
                break;

            case 'toggle_server':
                $url = trim($_POST['url'] ?? '');
                $enabled = filter_var($_POST['enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]) ?? false;

                $found = false;
                foreach ($servers as &$server) {
                    if ($server['url'] === $url) {
                        $server['enabled'] = $enabled;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    throw new Exception('Server URL not found to toggle status.');
                }
                $status_text = $enabled ? 'enabled' : 'disabled';
                $message = "Server status updated to {$status_text}.";
                break;

            default:
                throw new Exception('Invalid action specified.');
        }

        if (saveServers($pdo, $servers)) {
            $response = ['success' => true, 'message' => $message];
        } else {
            throw new Exception('Failed to save the updated server list.');
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
