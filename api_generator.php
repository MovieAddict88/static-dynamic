<?php
require_once 'check_login.php';
require_once 'config.php';
require_once 'utils.php';

// Function to fetch data from TMDB API
function fetch_tmdb_data($endpoint, $api_key) {
    $url = "https://api.themoviedb.org/3{$endpoint}?api_key={$api_key}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

// Function to get movie certification
function get_movie_certification($release_dates) {
    if (!isset($release_dates['results'])) return 'N/A';
    foreach ($release_dates['results'] as $result) {
        if ($result['iso_3166_1'] == 'US') {
            foreach ($result['release_dates'] as $release) {
                if ($release['type'] == 3 || $release['type'] == 4) { // Theatrical or Theatrical (limited)
                    return $release['certification'];
                }
            }
        }
    }
    return 'N/A';
}

// Function to format duration
function format_duration($minutes) {
    if (!$minutes) return "Unknown";
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%02d:%02d:00', $hours, $mins);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $tmdb_id = $_POST['tmdb_id'] ?? 0;
    $api_key = $_POST['api_key'] ?? '';

    if (empty($type) || empty($tmdb_id) || empty($api_key)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
        exit;
    }

    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$link) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    if ($type === 'movie') {
        $movie_data = fetch_tmdb_data("/movie/{$tmdb_id}", $api_key);
        $release_dates = fetch_tmdb_data("/movie/{$tmdb_id}/release_dates", $api_key);

        if (!$movie_data) {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch movie data from TMDB.']);
            exit;
        }

        // Insert into content table
        $stmt = mysqli_prepare($link, "INSERT INTO content (tmdb_id, title, description, poster, thumbnail, type, year, duration, rating, parental_rating, country) VALUES (?, ?, ?, ?, ?, 'movie', ?, ?, ?, ?, ?)");

        $poster_path = "https://image.tmdb.org/t/p/w500" . $movie_data['poster_path'];
        $thumbnail_path = "https://image.tmdb.org/t/p/w500" . $movie_data['backdrop_path'];
        $year = substr($movie_data['release_date'], 0, 4);
        $duration = format_duration($movie_data['runtime']);
        $rating = $movie_data['vote_average'];
        $parental_rating = get_movie_certification($release_dates);
        $country = $movie_data['production_countries'][0]['name'] ?? '';

        mysqli_stmt_bind_param($stmt, "isssssidss", $tmdb_id, $movie_data['title'], $movie_data['overview'], $poster_path, $thumbnail_path, $year, $duration, $rating, $parental_rating, $country);

        if (mysqli_stmt_execute($stmt)) {
            $content_id = mysqli_insert_id($link);

            // Insert genres
            foreach ($movie_data['genres'] as $genre) {
                // Check if genre exists
                $genre_stmt = mysqli_prepare($link, "SELECT id FROM genres WHERE name = ?");
                mysqli_stmt_bind_param($genre_stmt, "s", $genre['name']);
                mysqli_stmt_execute($genre_stmt);
                $result = mysqli_stmt_get_result($genre_stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $genre_id = $row['id'];
                } else {
                    // Insert new genre
                    $insert_genre_stmt = mysqli_prepare($link, "INSERT INTO genres (name) VALUES (?)");
                    mysqli_stmt_bind_param($insert_genre_stmt, "s", $genre['name']);
                    mysqli_stmt_execute($insert_genre_stmt);
                    $genre_id = mysqli_insert_id($link);
                }

                // Insert into content_genres
                $content_genre_stmt = mysqli_prepare($link, "INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                mysqli_stmt_bind_param($content_genre_stmt, "ii", $content_id, $genre_id);
                mysqli_stmt_execute($content_genre_stmt);
            }

            // Insert servers
            if (isset($_POST['servers'])) {
                $servers = json_decode($_POST['servers'], true);
                if (is_array($servers)) {
                    foreach ($servers as $server) {
                        $server_stmt = mysqli_prepare($link, "INSERT INTO servers (content_id, name, url) VALUES (?, ?, ?)");
                        mysqli_stmt_bind_param($server_stmt, "iss", $content_id, $server['name'], $server['url']);
                        mysqli_stmt_execute($server_stmt);
                    }
                }
            }

            echo json_encode(['success' => true, 'message' => 'Movie added successfully.']);
        }
    }

    if ($type === 'series') {
            $series_data = fetch_tmdb_data("/tv/{$tmdb_id}", $api_key);
            $content_ratings = fetch_tmdb_data("/tv/{$tmdb_id}/content_ratings", $api_key);

            if (!$series_data) {
                echo json_encode(['success' => false, 'message' => 'Failed to fetch series data from TMDB.']);
                exit;
            }

            // Insert into content table
            $stmt = mysqli_prepare($link, "INSERT INTO content (tmdb_id, title, description, poster, thumbnail, type, year, duration, rating, parental_rating, country) VALUES (?, ?, ?, ?, ?, 'series', ?, ?, ?, ?, ?)");

            $poster_path = "https://image.tmdb.org/t/p/w500" . $series_data['poster_path'];
            $thumbnail_path = "https://image.tmdb.org/t/p/w500" . $series_data['backdrop_path'];
            $year = substr($series_data['first_air_date'], 0, 4);
            $duration = format_duration($series_data['episode_run_time'][0] ?? 0);
            $rating = $series_data['vote_average'];
            $parental_rating = get_tv_certification($content_ratings);
            $country = $series_data['origin_country'][0] ?? '';

            mysqli_stmt_bind_param($stmt, "isssssidss", $tmdb_id, $series_data['name'], $series_data['overview'], $poster_path, $thumbnail_path, $year, $duration, $rating, $parental_rating, $country);

            if (mysqli_stmt_execute($stmt)) {
                $content_id = mysqli_insert_id($link);

                // Insert genres
                foreach ($series_data['genres'] as $genre) {
                    $genre_id = get_or_create_genre($link, $genre['name']);
                    $content_genre_stmt = mysqli_prepare($link, "INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                    mysqli_stmt_bind_param($content_genre_stmt, "ii", $content_id, $genre_id);
                    mysqli_stmt_execute($content_genre_stmt);
                }

                // Insert seasons and episodes
                $seasons_to_include = !empty($_POST['seasons']) ? explode(',', $_POST['seasons']) : array_column($series_data['seasons'], 'season_number');
                $server_templates = isset($_POST['servers']) ? json_decode($_POST['servers'], true) : [];

                foreach ($seasons_to_include as $season_num) {
                    $season_data = fetch_tmdb_data("/tv/{$tmdb_id}/season/{$season_num}", $api_key);
                    if ($season_data) {
                        $season_stmt = mysqli_prepare($link, "INSERT INTO seasons (content_id, season_number, poster) VALUES (?, ?, ?)");
                        $season_poster = "https://image.tmdb.org/t/p/w500" . $season_data['poster_path'];
                        mysqli_stmt_bind_param($season_stmt, "iis", $content_id, $season_num, $season_poster);
                        mysqli_stmt_execute($season_stmt);
                        $season_id = mysqli_insert_id($link);

                        foreach ($season_data['episodes'] as $episode_data) {
                            $episode_stmt = mysqli_prepare($link, "INSERT INTO episodes (season_id, episode_number, title, description, thumbnail, duration) VALUES (?, ?, ?, ?, ?, ?)");
                            $episode_thumbnail = "https://image.tmdb.org/t/p/w500" . $episode_data['still_path'];
                            $episode_duration = format_duration($episode_data['runtime']);
                            mysqli_stmt_bind_param($episode_stmt, "iissss", $season_id, $episode_data['episode_number'], $episode_data['name'], $episode_data['overview'], $episode_thumbnail, $episode_duration);
                            mysqli_stmt_execute($episode_stmt);
                            $episode_id = mysqli_insert_id($link);

                            // Add servers from templates
                            foreach ($server_templates as $template) {
                                $server_url = str_replace(['{season}', '{episode}'], [$season_num, $episode_data['episode_number']], $template['url']);
                                $server_stmt = mysqli_prepare($link, "INSERT INTO servers (episode_id, name, url) VALUES (?, ?, ?)");
                                mysqli_stmt_bind_param($server_stmt, "iss", $episode_id, $template['name'], $server_url);
                                mysqli_stmt_execute($server_stmt);
                            }
                        }
                    }
                }
                echo json_encode(['success' => true, 'message' => 'Series added successfully.']);
            } else {
                 echo json_encode(['success' => false, 'message' => 'Failed to insert series into database.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid content type.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }
}

function get_tv_certification($content_ratings) {
    if (!isset($content_ratings['results'])) return 'N/A';
    foreach ($content_ratings['results'] as $result) {
        if ($result['iso_3166_1'] == 'US') {
            return $result['rating'];
        }
    }
    return 'N/A';
}
?>