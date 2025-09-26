<?php
require_once '../config.php';

// Increase execution time and memory limit for this script
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '1024M'); // 1 GB

header('Content-Type: application/json');

function send_json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    send_json_response(['success' => false, 'message' => 'File upload error.'], 400);
}

$file_path = $_FILES['import_file']['tmp_name'];
$json_data = file_get_contents($file_path);
$data = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    send_json_response(['success' => false, 'message' => 'Invalid JSON format: ' . json_last_error_msg()], 400);
}

// Check for the expected 'Categories' structure
if (!isset($data['Categories']) || !is_array($data['Categories'])) {
    send_json_response(['success' => false, 'message' => 'Invalid data structure. Expected a root object with a "Categories" array.'], 400);
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    $total_processed = 0;
    $batch_size = 100; // Process 100 entries at a time

    foreach ($data['Categories'] as $category) {
        $main_category = $category['MainCategory'] ?? 'Unknown';
        $entries = $category['Entries'] ?? [];

        foreach (array_chunk($entries, $batch_size) as $batch) {
            foreach ($batch as $entry) {
                // This is a simplified insertion logic.
                // A more robust version would handle movies, series, seasons, and episodes correctly.

                $content_type = 'live'; // Default
                if ($main_category === 'Movies') $content_type = 'movie';
                if ($main_category === 'TV Series') $content_type = 'series';

                $sql = "INSERT INTO content (title, description, poster_url, content_type, release_date) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $entry['Title'] ?? 'Untitled',
                    $entry['Description'] ?? null,
                    $entry['Poster'] ?? null,
                    $content_type,
                    isset($entry['Year']) ? $entry['Year'] . '-01-01' : null
                ]);
                $total_processed++;
            }
        }
    }

    $pdo->commit();
    send_json_response(['success' => true, 'message' => "Successfully imported {$total_processed} entries."]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    send_json_response(['success' => false, 'message' => 'Database error during import: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    send_json_response(['success' => false, 'message' => 'An unexpected error occurred during import: ' . $e->getMessage()], 500);
}
?>