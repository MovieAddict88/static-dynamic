<?php
// This file will handle TMDB API interactions and database insertions.
require_once '../config.php';

// Function to fetch data from TMDB API
function fetchTMDB($endpoint, $apiKey, $params = []) {
    $base_url = "https://api.themoviedb.org/3";

    // Add required parameters
    $params['api_key'] = $apiKey;
    if (!isset($params['append_to_response'])) {
        $params['append_to_response'] = 'credits,videos,release_dates,content_ratings';
    }

    $queryString = http_build_query($params);
    $url = "{$base_url}{$endpoint}?{$queryString}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CineCrazePHPApp/1.0');
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode != 200) {
        return ['error' => 'TMDB API request failed', 'status_code' => $httpcode, 'response' => json_decode($output, true)];
    }

    return json_decode($output, true);
}

// Function to add a movie from TMDB to the database
function addMovieFromTmdb($tmdbId) {
    // In a real application, this should be managed better.
    $apiKey = 'ec926176bf467b3f7735e3154238c161';
    $movieData = fetchTMDB("/movie/{$tmdbId}", $apiKey);

    if (!$movieData || !empty($movieData['error'])) {
        return "Error: Could not fetch data for TMDB ID: {$tmdbId}. The ID might be invalid or the API key is wrong.";
    }

    $pdo = connect_db();
    if (!$pdo) {
        return "Error: Database connection failed.";
    }

    try {
        $pdo->beginTransaction();

        // Check if movie already exists
        $stmt = $pdo->prepare("SELECT id FROM content WHERE tmdb_id = ? AND type = 'movie'");
        $stmt->execute([$tmdbId]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            return "Info: Movie with TMDB ID {$tmdbId} already exists in the database.";
        }

        // Fetch auto-embed servers
        $server_stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_embed_servers'");
        $server_stmt->execute();
        $servers_json = $server_stmt->fetchColumn();
        $servers = $servers_json ? json_decode($servers_json, true) : [];
        $server_count = 0;

        // Insert into content table
        $stmt = $pdo->prepare(
            "INSERT INTO content (tmdb_id, type, title, description, poster_url, thumbnail_url, release_year, rating, duration, parental_rating)
             VALUES (?, 'movie', ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $tmdbId,
            $movieData['title'],
            $movieData['overview'],
            isset($movieData['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $movieData['poster_path'] : null,
            isset($movieData['backdrop_path']) ? 'https://image.tmdb.org/t/p/w1280' . $movieData['backdrop_path'] : null,
            isset($movieData['release_date']) ? date('Y', strtotime($movieData['release_date'])) : null,
            $movieData['vote_average'],
            $movieData['runtime'] ? gmdate("H:i:s", $movieData['runtime'] * 60) : null,
            getMovieCertification($movieData)
        ]);
        $contentId = $pdo->lastInsertId();

        // Insert genres
        if (!empty($movieData['genres'])) {
            foreach ($movieData['genres'] as $genreData) {
                $stmt = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
                $stmt->execute([$genreData['name']]);
                if (!($genre = $stmt->fetch())) {
                    $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
                    $stmt->execute([$genreData['name']]);
                    $genreId = $pdo->lastInsertId();
                } else {
                    $genreId = $genre['id'];
                }
                $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                $stmt->execute([$contentId, $genreId]);
            }
        }

        // Insert auto-generated server links
        if (!empty($servers)) {
            $server_insert_stmt = $pdo->prepare("INSERT INTO servers (content_id, name, url, quality) VALUES (?, ?, ?, ?)");
            foreach ($servers as $server_url) {
                $host = parse_url($server_url, PHP_URL_HOST) ?? 'Auto Server';
                $final_url = $server_url . $tmdbId;
                $server_insert_stmt->execute([$contentId, $host, $final_url, 'HD']);
                $server_count++;
            }
        }

        $pdo->commit();
        $message = "Success: Movie '{$movieData['title']}' was added.";
        if ($server_count > 0) {
            $message .= " {$server_count} embed link(s) auto-generated.";
        } else {
            $message .= " Warning: No embed links generated as no servers are configured in Settings.";
        }
        return $message;

    } catch (Exception $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        return "Database error: " . $e->getMessage();
    }
}

// Helper function to get US certification
function getMovieCertification($movieData) {
    if (isset($movieData['release_dates']['results'])) {
        foreach ($movieData['release_dates']['results'] as $result) {
            if ($result['iso_3166_1'] == 'US') {
                // Type 3 is theatrical release
                foreach($result['release_dates'] as $release) {
                    if($release['type'] == 3 && !empty($release['certification'])) return $release['certification'];
                }
            }
        }
    }
    return 'NR';
}

function getSeriesCertification($seriesData) {
    if (isset($seriesData['content_ratings']['results'])) {
        foreach ($seriesData['content_ratings']['results'] as $result) {
            if ($result['iso_3166_1'] == 'US' && !empty($result['rating'])) {
                return $result['rating'];
            }
        }
    }
    return 'NR';
}


// Function to add a TV Series from TMDB to the database
function addSeriesFromTmdb($tmdbId, $seasonsInput = '') {
    $apiKey = 'ec926176bf467b3f7735e3154238c161';
    $seriesData = fetchTMDB("/tv/{$tmdbId}", $apiKey);

    if (!$seriesData || !empty($seriesData['error'])) {
        return "Error: Could not fetch data for TV Series TMDB ID: {$tmdbId}.";
    }

    $pdo = connect_db();
    if (!$pdo) return "Error: Database connection failed.";

    try {
        $pdo->beginTransaction();

        // Check if series already exists
        $stmt = $pdo->prepare("SELECT id FROM content WHERE tmdb_id = ? AND type = 'series'");
        $stmt->execute([$tmdbId]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            return "Info: Series with TMDB ID {$tmdbId} already exists.";
        }

        // Fetch auto-embed servers
        $server_stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_embed_servers'");
        $server_stmt->execute();
        $servers_json = $server_stmt->fetchColumn();
        $servers = $servers_json ? json_decode($servers_json, true) : [];
        $server_count = 0;

        // Insert into content table
        $stmt = $pdo->prepare(
            "INSERT INTO content (tmdb_id, type, title, description, poster_url, thumbnail_url, release_year, rating, parental_rating)
             VALUES (?, 'series', ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $tmdbId,
            $seriesData['name'],
            $seriesData['overview'],
            isset($seriesData['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $seriesData['poster_path'] : null,
            isset($seriesData['backdrop_path']) ? 'https://image.tmdb.org/t/p/w1280' . $seriesData['backdrop_path'] : null,
            isset($seriesData['first_air_date']) ? date('Y', strtotime($seriesData['first_air_date'])) : null,
            $seriesData['vote_average'],
            getSeriesCertification($seriesData)
        ]);
        $contentId = $pdo->lastInsertId();

        // Insert genres
        if (!empty($seriesData['genres'])) {
            foreach ($seriesData['genres'] as $genreData) {
                $stmt = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
                $stmt->execute([$genreData['name']]);
                if(!($genre = $stmt->fetch())) {
                    $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
                    $stmt->execute([$genreData['name']]);
                    $genreId = $pdo->lastInsertId();
                } else {
                    $genreId = $genre['id'];
                }
                $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                $stmt->execute([$contentId, $genreId]);
            }
        }

        // Determine which seasons to fetch
        $seasonsToFetch = [];
        if (!empty($seasonsInput)) {
            $seasonsToFetch = array_map('intval', explode(',', $seasonsInput));
        } else {
            foreach ($seriesData['seasons'] as $season) {
                if ($season['season_number'] > 0) { // Skip "Specials" (season 0)
                    $seasonsToFetch[] = $season['season_number'];
                }
            }
        }

        // Prepare server insert statement once
        if (!empty($servers)) {
            $server_insert_stmt = $pdo->prepare("INSERT INTO servers (episode_id, name, url, quality) VALUES (?, ?, ?, ?)");
        }

        // Process each season and its episodes
        foreach ($seasonsToFetch as $seasonNumber) {
            $seasonData = fetchTMDB("/tv/{$tmdbId}/season/{$seasonNumber}", $apiKey);
            if (!$seasonData || !empty($seasonData['error'])) continue;

            $stmt = $pdo->prepare("INSERT INTO seasons (content_id, season_number, title, poster_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$contentId, $seasonNumber, $seasonData['name'], isset($seasonData['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $seasonData['poster_path'] : null]);
            $seasonId = $pdo->lastInsertId();

            if (!empty($seasonData['episodes'])) {
                foreach ($seasonData['episodes'] as $episodeData) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO episodes (season_id, episode_number, title, description, thumbnail_url, duration)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $seasonId, $episodeData['episode_number'], $episodeData['name'], $episodeData['overview'],
                        isset($episodeData['still_path']) ? 'https://image.tmdb.org/t/p/w780' . $episodeData['still_path'] : null,
                        $episodeData['runtime'] ? gmdate("H:i:s", $episodeData['runtime'] * 60) : null
                    ]);
                    $episodeId = $pdo->lastInsertId();

                    // Insert auto-generated server links for the episode
                    if (!empty($servers)) {
                        foreach ($servers as $server_url) {
                            $host = parse_url($server_url, PHP_URL_HOST) ?? 'Auto Server';
                            $final_url = $server_url . $tmdbId . '-s' . $seasonNumber . '-e' . $episodeData['episode_number'];
                            $server_insert_stmt->execute([$episodeId, $host, $final_url, 'HD']);
                            $server_count++;
                        }
                    }
                }
            }
        }

        $pdo->commit();
        $message = "Success: Series '{$seriesData['name']}' and its seasons/episodes were added.";
        if ($server_count > 0) {
            $message .= " {$server_count} embed link(s) auto-generated.";
        } else {
            $message .= " Warning: No embed links generated as no servers are configured in Settings.";
        }
        return $message;

    } catch (Exception $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        return "Database error: " . $e->getMessage();
    }
}
?>
