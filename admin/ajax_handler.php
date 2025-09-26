<?php
require_once '../config.php';
require_once '../includes/db_handler.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$response = ['success' => false, 'message' => 'Invalid action.'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($action)) {
    switch ($action) {
        case 'generate_movie':
            $tmdbId = $_POST['tmdb_id'] ?? null;
            $servers = isset($_POST['servers']) ? json_decode($_POST['servers'], true) : [];
            if ($tmdbId) {
                $response = addMovieFromTMDB($tmdbId, $servers);
            } else {
                $response['message'] = 'TMDB ID is required.';
            }
            break;

        case 'generate_series':
            $tmdbId = $_POST['tmdb_id'] ?? null;
            $seasons = isset($_POST['seasons']) && !empty($_POST['seasons']) ? explode(',', $_POST['seasons']) : [];
            $servers = isset($_POST['servers']) ? json_decode($_POST['servers'], true) : [];
            if ($tmdbId) {
                $response = addSeriesFromTMDB($tmdbId, $seasons, $servers);
            } else {
                $response['message'] = 'TMDB ID is required.';
            }
            break;

        // Future actions for manual input, bulk operations, etc. will be added here.

        default:
            $response['message'] = 'Unknown action specified.';
            break;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>