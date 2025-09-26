<?php
require_once '../config.php';
require_once 'tmdb_handler.php'; // Reuse the TMDB fetching and insertion logic

// Increase execution time and memory limit for this script
ini_set('max_execution_time', 600); // 10 minutes
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

$post_data = json_decode(file_get_contents('php://input'), true);

$bulk_type = $post_data['bulk_type'] ?? null;
$year = filter_var($post_data['year'] ?? null, FILTER_VALIDATE_INT);
$pages = filter_var($post_data['pages'] ?? 1, FILTER_VALIDATE_INT);
$skip_duplicates = filter_var($post_data['skip_duplicates'] ?? true, FILTER_VALIDATE_BOOLEAN);

if (!$bulk_type || !$year || !$pages) {
    send_json_response(['success' => false, 'message' => 'Missing or invalid parameters for bulk generation.'], 400);
}

try {
    $pdo = getDBConnection();
    $generated_count = 0;
    $skipped_count = 0;

    for ($page = 1; $page <= $pages; $page++) {
        $params = [
            'page' => $page,
            'sort_by' => 'popularity.desc'
        ];

        if ($bulk_type === 'movie') {
            $params['primary_release_year'] = $year;
        } else {
            $params['first_air_date_year'] = $year;
        }

        $results = fetchTMDB("/discover/{$bulk_type}", $params);

        if (!$results || empty($results['results'])) {
            break; // No more results
        }

        foreach ($results['results'] as $item) {
            $tmdb_id = $item['id'];

            if ($skip_duplicates) {
                $stmt = $pdo->prepare("SELECT id FROM content WHERE tmdb_id = ?");
                $stmt->execute([$tmdb_id]);
                if ($stmt->fetch()) {
                    $skipped_count++;
                    continue; // Skip if already exists
                }
            }

            // This is a simplified insertion. A full implementation would reuse the
            // detailed movie/series generation logic from tmdb_handler.php
            $content_type = ($bulk_type === 'movie') ? 'movie' : 'series';

            $sql = "INSERT INTO content (tmdb_id, title, content_type, release_date) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $tmdb_id,
                $item['title'] ?? $item['name'],
                $content_type,
                $item['release_date'] ?? $item['first_air_date']
            ]);
            $generated_count++;
        }
    }

    send_json_response([
        'success' => true,
        'message' => "Bulk generation complete. Generated: {$generated_count}, Skipped: {$skipped_count}."
    ]);

} catch (Exception $e) {
    send_json_response(['success' => false, 'message' => 'An error occurred during bulk generation: ' . $e->getMessage()], 500);
}
?>