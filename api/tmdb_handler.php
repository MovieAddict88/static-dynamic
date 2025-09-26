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
$type = $post_data['type'] ?? null;
$tmdb_id = filter_var($post_data['tmdb_id'] ?? null, FILTER_VALIDATE_INT);

if (!$type || !$tmdb_id) {
    send_json_response(['success' => false, 'message' => 'Missing or invalid parameters.'], 400);
}

function fetchTMDB($endpoint) {
    $url = "https://api.themoviedb.org/3{$endpoint}?api_key=" . TMDB_API_KEY;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id FROM content WHERE tmdb_id = ?");
    $stmt->execute([$tmdb_id]);
    if ($stmt->fetch()) {
        send_json_response(['success' => false, 'message' => "Content with TMDB ID {$tmdb_id} already exists."]);
    }

    if ($type === 'movie') {
        $movie_data = fetchTMDB("/movie/{$tmdb_id}");
        if (!$movie_data || isset($movie_data['success']) && $movie_data['success'] === false) {
            send_json_response(['success' => false, 'message' => "Could not fetch movie data from TMDB for ID {$tmdb_id}."], 404);
        }

        $pdo->beginTransaction();

        $sql = "INSERT INTO content (tmdb_id, title, description, poster_url, thumbnail_url, content_type, release_date, rating, duration_minutes) VALUES (?, ?, ?, ?, ?, 'movie', ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $tmdb_id,
            $movie_data['title'],
            $movie_data['overview'],
            $movie_data['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie_data['poster_path'] : null,
            $movie_data['backdrop_path'] ? 'https://image.tmdb.org/t/p/w500' . $movie_data['backdrop_path'] : null,
            $movie_data['release_date'],
            $movie_data['vote_average'],
            $movie_data['runtime']
        ]);
        $content_id = $pdo->lastInsertId();

        foreach ($movie_data['genres'] as $genre_data) {
            $stmt = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
            $stmt->execute([$genre_data['name']]);
            $genre_id = $stmt->fetchColumn();

            if (!$genre_id) {
                $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
                $stmt->execute([$genre_data['name']]);
                $genre_id = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
            $stmt->execute([$content_id, $genre_id]);
        }

        $pdo->commit();
        send_json_response(['success' => true, 'message' => "Movie '{$movie_data['title']}' added successfully."]);

    } elseif ($type === 'series') {
        $series_data = fetchTMDB("/tv/{$tmdb_id}");
        if (!$series_data || isset($series_data['success']) && $series_data['success'] === false) {
            send_json_response(['success' => false, 'message' => "Could not fetch series data from TMDB for ID {$tmdb_id}."], 404);
        }

        $pdo->beginTransaction();

        // Insert into content table
        $sql = "INSERT INTO content (tmdb_id, title, description, poster_url, thumbnail_url, content_type, release_date, rating, duration_minutes) VALUES (?, ?, ?, ?, ?, 'series', ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $tmdb_id,
            $series_data['name'],
            $series_data['overview'],
            $series_data['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $series_data['poster_path'] : null,
            $series_data['backdrop_path'] ? 'https://image.tmdb.org/t/p/w500' . $series_data['backdrop_path'] : null,
            $series_data['first_air_date'],
            $series_data['vote_average'],
            $series_data['episode_run_time'][0] ?? null
        ]);
        $content_id = $pdo->lastInsertId();

        // Insert genres
        foreach ($series_data['genres'] as $genre_data) {
            $stmt = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
            $stmt->execute([$genre_data['name']]);
            $genre_id = $stmt->fetchColumn();

            if (!$genre_id) {
                $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
                $stmt->execute([$genre_data['name']]);
                $genre_id = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
            $stmt->execute([$content_id, $genre_id]);
        }

        // Insert seasons and episodes
        foreach ($series_data['seasons'] as $season_data) {
            if ($season_data['season_number'] == 0) continue; // Skip "Specials"

            $season_details = fetchTMDB("/tv/{$tmdb_id}/season/{$season_data['season_number']}");
            if(!$season_details) continue;

            $sql = "INSERT INTO seasons (content_id, season_number, poster_url) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $content_id,
                $season_data['season_number'],
                $season_data['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $season_data['poster_path'] : null
            ]);
            $season_id = $pdo->lastInsertId();

            if (isset($season_details['episodes'])) {
                foreach ($season_details['episodes'] as $episode_data) {
                    $sql = "INSERT INTO episodes (season_id, episode_number, title, description, thumbnail_url, duration_minutes) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $season_id,
                        $episode_data['episode_number'],
                        $episode_data['name'],
                        $episode_data['overview'],
                        $episode_data['still_path'] ? 'https://image.tmdb.org/t/p/w500' . $episode_data['still_path'] : null,
                        $episode_data['runtime']
                    ]);
                }
            }
        }

        $pdo->commit();
        send_json_response(['success' => true, 'message' => "Series '{$series_data['name']}' added successfully."]);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    send_json_response(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    send_json_response(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
}
?>