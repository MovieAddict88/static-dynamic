<?php
require_once 'auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// --- Configuration ---
require_once __DIR__ . '/../config.php';
$tmdb_base_url = 'https://api.themoviedb.org/3';

// --- Helper Functions ---

/**
 * Fetches data from the TMDB API.
 * @param string $endpoint The API endpoint (e.g., '/movie/550')
 * @return array|null The decoded JSON data or null on error.
 */
function fetch_from_tmdb($endpoint) {
    global $tmdb_base_url;
    $url = "{$tmdb_base_url}{$endpoint}?api_key=" . TMDB_API_KEY . "&language=en-US";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        return null;
    }
    return json_decode($output, true);
}

/**
 * Inserts a genre if it doesn't exist and returns its ID.
 * @param PDO $pdo The PDO database connection object.
 * @param string $name The name of the genre.
 * @return int The genre ID.
 */
function get_or_create_genre_id($pdo, $name) {
    $stmt = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
    $stmt->execute([$name]);
    $genre = $stmt->fetch();

    if ($genre) {
        return $genre['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
        $stmt->execute([$name]);
        return $pdo->lastInsertId();
    }
}


// --- Main Logic ---

$action = $_POST['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Invalid action.'];

if ($action === 'generate') {
    $type = $_POST['type'] ?? '';
    $tmdb_id = (int)($_POST['tmdb_id'] ?? 0);

    if (empty($type) || $tmdb_id <= 0) {
        $response['message'] = 'Type and TMDB ID are required.';
        echo json_encode($response);
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($type === 'movie') {
            $movie_data = fetch_from_tmdb("/movie/{$tmdb_id}");
            if (!$movie_data) throw new Exception("Could not fetch movie data from TMDB for ID: {$tmdb_id}");

            $stmt = $pdo->prepare("INSERT INTO content (tmdb_id, type, title, description, poster, thumbnail, year, duration, rating, parental_rating, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $tmdb_id,
                'movie',
                $movie_data['title'],
                $movie_data['overview'],
                'https://image.tmdb.org/t/p/w500' . $movie_data['poster_path'],
                'https://image.tmdb.org/t/p/w780' . $movie_data['backdrop_path'],
                (int)substr($movie_data['release_date'], 0, 4),
                $movie_data['runtime'] . ' min',
                $movie_data['vote_average'],
                $movie_data['adult'] ? 'R' : 'PG-13', // Simplified
                $movie_data['production_countries'][0]['name'] ?? ''
            ]);
            $content_id = $pdo->lastInsertId();

            foreach ($movie_data['genres'] as $genre_data) {
                $genre_id = get_or_create_genre_id($pdo, $genre_data['name']);
                $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                $stmt->execute([$content_id, $genre_id]);
            }

            $response = ['status' => 'success', 'message' => "Movie '{$movie_data['title']}' generated successfully."];

        } elseif ($type === 'series') {
            $series_data = fetch_from_tmdb("/tv/{$tmdb_id}");
            if (!$series_data) throw new Exception("Could not fetch series data from TMDB for ID: {$tmdb_id}");

            $stmt = $pdo->prepare("INSERT INTO content (tmdb_id, type, title, description, poster, thumbnail, year, duration, rating, parental_rating, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $tmdb_id,
                'series',
                $series_data['name'],
                $series_data['overview'],
                'https://image.tmdb.org/t/p/w500' . $series_data['poster_path'],
                'https://image.tmdb.org/t/p/w780' . $series_data['backdrop_path'],
                (int)substr($series_data['first_air_date'], 0, 4),
                $series_data['episode_run_time'][0] ?? 'N/A' . ' min',
                $series_data['vote_average'],
                $series_data['adult'] ? 'TV-MA' : 'TV-14', // Simplified
                $series_data['origin_country'][0] ?? ''
            ]);
            $content_id = $pdo->lastInsertId();

            foreach ($series_data['genres'] as $genre_data) {
                $genre_id = get_or_create_genre_id($pdo, $genre_data['name']);
                $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                $stmt->execute([$content_id, $genre_id]);
            }

            // Fetch and insert seasons and episodes
            $seasons_to_fetch = $_POST['seasons'] ? explode(',', $_POST['seasons']) : range(1, $series_data['number_of_seasons']);

            foreach ($seasons_to_fetch as $season_num) {
                $season_num = (int)$season_num;
                $season_data = fetch_from_tmdb("/tv/{$tmdb_id}/season/{$season_num}");
                if(!$season_data) continue;

                $stmt = $pdo->prepare("INSERT INTO seasons (content_id, season_number, name, poster) VALUES (?, ?, ?, ?)");
                $stmt->execute([$content_id, $season_num, $season_data['name'], 'https://image.tmdb.org/t/p/w500' . $season_data['poster_path']]);
                $season_id = $pdo->lastInsertId();

                foreach ($season_data['episodes'] as $episode_data) {
                    $stmt = $pdo->prepare("INSERT INTO episodes (season_id, episode_number, title, description, thumbnail, duration) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $season_id,
                        $episode_data['episode_number'],
                        $episode_data['name'],
                        $episode_data['overview'],
                        'https://image.tmdb.org/t/p/w500' . $episode_data['still_path'],
                        $episode_data['runtime'] . ' min'
                    ]);
                }
            }
            $response = ['status' => 'success', 'message' => "Series '{$series_data['name']}' generated successfully."];
        }

        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = 'Generation failed: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>