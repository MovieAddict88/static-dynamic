<?php
require_once '../config.php';

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

// --- Basic Validation ---
$required_fields = ['type', 'title', 'subcategory', 'servers'];
foreach ($required_fields as $field) {
    if (empty($post_data[$field])) {
        send_json_response(['success' => false, 'message' => "Missing required field: {$field}."], 400);
    }
}

if (!is_array($post_data['servers']) || count($post_data['servers']) === 0) {
    send_json_response(['success' => false, 'message' => 'At least one server is required.'], 400);
}

$type = $post_data['type'];
$title = trim($post_data['title']);
$subcategory = trim($post_data['subcategory']);
$description = trim($post_data['description'] ?? 'No description available.');
$poster_url = filter_var(trim($post_data['image'] ?? ''), FILTER_VALIDATE_URL) ? trim($post_data['image']) : null;
$year = filter_var(trim($post_data['year'] ?? ''), FILTER_VALIDATE_INT) ? trim($post_data['year']) : null;
$rating = filter_var(trim($post_data['rating'] ?? ''), FILTER_VALIDATE_FLOAT) ? trim($post_data['rating']) : null;
$parental_rating = trim($post_data['parental_rating'] ?? null);
$servers = $post_data['servers'];


try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Insert into content table
    $sql = "INSERT INTO content (title, description, poster_url, thumbnail_url, content_type, release_date, rating, parental_rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $title,
        $description,
        $poster_url,
        $poster_url, // Using poster for thumbnail as well
        $type,
        $year ? "{$year}-01-01" : null,
        $rating,
        $parental_rating
    ]);
    $content_id = $pdo->lastInsertId();

    // Handle Genre
    $stmt = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
    $stmt->execute([$subcategory]);
    $genre_id = $stmt->fetchColumn();

    if (!$genre_id) {
        $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
        $stmt->execute([$subcategory]);
        $genre_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
    $stmt->execute([$content_id, $genre_id]);

    // Handle Servers
    foreach ($servers as $server) {
        if (!empty($server['name']) && !empty($server['url'])) {
            $sql = "INSERT INTO servers (content_id, name, url, quality) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $content_id,
                $server['name'],
                $server['url'],
                $server['quality'] ?? null
            ]);
        }
    }

    // Handle Series
    if ($type === 'series' && isset($post_data['seasons']) && is_array($post_data['seasons'])) {
        foreach ($post_data['seasons'] as $season_data) {
            if (empty($season_data['season_number'])) continue;

            // Insert season
            $sql = "INSERT INTO seasons (content_id, season_number) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$content_id, $season_data['season_number']]);
            $season_id = $pdo->lastInsertId();

            // Insert episodes for this season
            if (isset($season_data['episodes']) && is_array($season_data['episodes'])) {
                foreach ($season_data['episodes'] as $episode_data) {
                    if (empty($episode_data['episode_number']) || empty($episode_data['title'])) continue;

                    $sql = "INSERT INTO episodes (season_id, episode_number, title) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$season_id, $episode_data['episode_number'], $episode_data['title']]);
                    $episode_id = $pdo->lastInsertId();

                    // Insert servers for this episode
                    if (isset($episode_data['servers']) && is_array($episode_data['servers'])) {
                        foreach ($episode_data['servers'] as $server) {
                             if (!empty($server['name']) && !empty($server['url'])) {
                                $sql = "INSERT INTO servers (episode_id, name, url, quality) VALUES (?, ?, ?, ?)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$episode_id, $server['name'], $server['url'], $server['quality'] ?? null]);
                            }
                        }
                    }
                }
            }
        }
    }

    $pdo->commit();
    send_json_response(['success' => true, 'message' => "Content '{$title}' added successfully."]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    send_json_response(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    send_json_response(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
}
?>