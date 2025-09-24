<?php
// This file will handle TMDB API interactions and database insertions.
require_once '../config.php';

// Function to fetch data from TMDB API
function fetchTMDB($endpoint, $apiKey, $params = []) {
    $base_url = "https://api.themoviedb.org/3";

    // Add required parameters
    $params['api_key'] = $apiKey;
    if (!isset($params['append_to_response'])) {
        $params['append_to_response'] = 'credits,videos,release_dates';
    }

    $queryString = http_build_query($params);
    $url = "{$base_url}{$endpoint}?{$queryString}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CineCrazePHPApp/1.0'); // Good practice to set a user agent
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode != 200) {
        // Log error or handle it appropriately
        return ['error' => 'TMDB API request failed', 'status_code' => $httpcode, 'response' => json_decode($output, true)];
    }

    return json_decode($output, true);
}

// Function to add a movie from TMDB to the database
function addMovieFromTmdb($tmdbId) {
    // NOTE: This is a placeholder for the actual API key.
    // In a real application, this should be stored securely.
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
                // Find or create genre
                $stmt = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
                $stmt->execute([$genreData['name']]);
                $genre = $stmt->fetch();
                if ($genre) {
                    $genreId = $genre['id'];
                } else {
                    $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
                    $stmt->execute([$genreData['name']]);
                    $genreId = $pdo->lastInsertId();
                }
                // Link content to genre
                $stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                $stmt->execute([$contentId, $genreId]);
            }
        }

        $pdo->commit();
        return "Success: Movie '{$movieData['title']}' was added to the database.";

    } catch (Exception $e) {
        $pdo->rollBack();
        return "Database error: " . $e->getMessage();
    }
}

// Helper function to get US certification for a movie
function getMovieCertification($movieData) {
    if (isset($movieData['release_dates']['results'])) {
        foreach ($movieData['release_dates']['results'] as $result) {
            if ($result['iso_3166_1'] == 'US') {
                foreach($result['release_dates'] as $release) {
                    if($release['type'] == 3 && !empty($release['certification'])) return $release['certification'];
                }
            }
        }
    }
    return 'NR';
}

// Helper function to get US certification for a series
function getSeriesCertification($seriesData) {
    if (isset($seriesData['content_ratings']['results'])) {
        foreach ($seriesData['content_ratings']['results'] as $result) {
            if ($result['iso_3166_1'] == 'US') {
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
                $genre = $stmt->fetch();
                $genreId = $genre ? $genre['id'] : null;
                if (!$genreId) {
                    $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?)");
                    $stmt->execute([$genreData['name']]);
                    $genreId = $pdo->lastInsertId();
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
                if ($season['season_number'] > 0) { // Typically skip "Specials" (season 0)
                    $seasonsToFetch[] = $season['season_number'];
                }
            }
        }

        // Process each season and its episodes
        foreach ($seasonsToFetch as $seasonNumber) {
            $seasonData = fetchTMDB("/tv/{$tmdbId}/season/{$seasonNumber}", $apiKey);
            if (!$seasonData || !empty($seasonData['error'])) continue;

            // Insert season
            $stmt = $pdo->prepare("INSERT INTO seasons (content_id, season_number, title, poster_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $contentId,
                $seasonNumber,
                $seasonData['name'],
                isset($seasonData['poster_path']) ? 'https://image.tmdb.org/t/p/w500' . $seasonData['poster_path'] : null
            ]);
            $seasonId = $pdo->lastInsertId();

            // Insert episodes
            if (!empty($seasonData['episodes'])) {
                foreach ($seasonData['episodes'] as $episodeData) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO episodes (season_id, episode_number, title, description, thumbnail_url, duration)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $seasonId,
                        $episodeData['episode_number'],
                        $episodeData['name'],
                        $episodeData['overview'],
                        isset($episodeData['still_path']) ? 'https://image.tmdb.org/t/p/w780' . $episodeData['still_path'] : null,
                        $episodeData['runtime'] ? gmdate("H:i:s", $episodeData['runtime'] * 60) : null
                    ]);
                }
            }
        }

        $pdo->commit();
        return "Success: Series '{$seriesData['name']}' and its seasons/episodes were added.";

    } catch (Exception $e) {
        $pdo->rollBack();
        return "Database error: " . $e->getMessage();
    }
}
?>
