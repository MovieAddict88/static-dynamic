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

        case 'get_content':
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 50;
            $filter = $_POST['filter'] ?? 'all';
            $search = $_POST['search'] ?? '';
            $response = getAllContent($page, $limit, $filter, $search);
            break;

        case 'delete_content':
            $contentId = $_POST['id'] ?? null;
            if ($contentId) {
                $response = deleteContentById($contentId);
            } else {
                $response['message'] = 'Content ID is required.';
            }
            break;

        case 'update_content':
            $contentData = isset($_POST['contentData']) ? json_decode($_POST['contentData'], true) : null;
            if ($contentData && isset($contentData['id'])) {
                $response = updateContent($contentData);
            } else {
                $response['message'] = 'Valid content data is required.';
            }
            break;

        case 'import_json':
            if (isset($_FILES['jsonFile']) && $_FILES['jsonFile']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['jsonFile']['tmp_name'];
                $fileNameCmps = explode(".", $_FILES['jsonFile']['name']);
                $fileExtension = strtolower(end($fileNameCmps));

                if ($fileExtension === 'json') {
                    $jsonContent = file_get_contents($fileTmpPath);
                    $data = json_decode($jsonContent, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        // This function will be created in db_handler.php
                        $response = importDataFromJson($data);
                    } else {
                        $response['message'] = 'Error parsing JSON file: ' . json_last_error_msg();
                    }
                } else {
                    $response['message'] = 'Invalid file type. Only .json files are allowed.';
                }
            } else {
                $response['message'] = 'File upload failed. Error code: ' . ($_FILES['jsonFile']['error'] ?? 'Unknown');
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