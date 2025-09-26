<?php
require_once '../config.php';

/**
 * Fetches TMDB data and handles API requests.
 *
 * @param string $endpoint The TMDB API endpoint (e.g., '/movie/550').
 * @param array $params Optional query parameters.
 * @return array|null The decoded JSON response or null on failure.
 */
function fetchTMDB($endpoint, $params = []) {
    $apiKey = TMDB_API_KEY;
    $url = "https://api.themoviedb.org/3" . $endpoint . "?api_key=" . $apiKey;

    foreach ($params as $key => $value) {
        $url .= "&" . urlencode($key) . "=" . urlencode($value);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "accept: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        // In a real app, log this error
        return null;
    } else {
        return json_decode($response, true);
    }
}

/**
 * Adds a new movie to the database from TMDB data.
 *
 * @param int $tmdbId The TMDB ID of the movie.
 * @param array $additionalServers An array of additional server URLs.
 * @return array A status array with success and message keys.
 */
function addMovieFromTMDB($tmdbId, $additionalServers = []) {
    $conn = getDBConnection();

    // Check for duplicates
    $stmt = $conn->prepare("SELECT id FROM content WHERE tmdb_id = :tmdb_id AND type = 'movie'");
    $stmt->execute(['tmdb_id' => $tmdbId]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Movie with this TMDB ID already exists.'];
    }

    $movieData = fetchTMDB("/movie/{$tmdbId}");
    if (!$movieData) {
        return ['success' => false, 'message' => 'Failed to fetch movie data from TMDB.'];
    }

    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare(
            "INSERT INTO content (tmdb_id, type, title, description, poster_path, backdrop_path, release_date, year, runtime, rating, parental_rating, genres, country)
             VALUES (:tmdb_id, 'movie', :title, :description, :poster_path, :backdrop_path, :release_date, :year, :runtime, :rating, :parental_rating, :genres, :country)"
        );

        $year = !empty($movieData['release_date']) ? date('Y', strtotime($movieData['release_date'])) : null;
        $genres = json_encode(array_column($movieData['genres'] ?? [], 'name'));
        $country = $movieData['production_countries'][0]['name'] ?? null;

        $stmt->execute([
            ':tmdb_id' => $tmdbId,
            ':title' => $movieData['title'],
            ':description' => $movieData['overview'],
            ':poster_path' => $movieData['poster_path'],
            ':backdrop_path' => $movieData['backdrop_path'],
            ':release_date' => $movieData['release_date'],
            ':year' => $year,
            ':runtime' => $movieData['runtime'],
            ':rating' => $movieData['vote_average'],
            ':parental_rating' => getMovieCertification($tmdbId),
            ':genres' => $genres,
            ':country' => $country
        ]);

        $contentId = $conn->lastInsertId();

        // Add servers
        $serverStmt = $conn->prepare("INSERT INTO servers (content_id, name, url, quality) VALUES (:content_id, :name, :url, :quality)");

        // Add auto-embed servers
        $embedSources = generateEmbedSources($tmdbId, 'movie');
        foreach ($embedSources as $source) {
            $serverStmt->execute([
                ':content_id' => $contentId,
                ':name' => $source['name'],
                ':url' => $source['url'],
                ':quality' => $source['quality']
            ]);
        }

        // Add additional servers
        foreach ($additionalServers as $server) {
             $serverStmt->execute([
                ':content_id' => $contentId,
                ':name' => $server['name'],
                ':url' => $server['url'],
                ':quality' => 'Auto'
            ]);
        }

        $conn->commit();
        return ['success' => true, 'message' => "Movie '{$movieData['title']}' added successfully."];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Adds a new TV series to the database from TMDB data.
 *
 * @param int $tmdbId The TMDB ID of the series.
 * @param array $seasonsToInclude An array of season numbers to include.
 * @param array $additionalServers An array of additional server URL templates.
 * @return array A status array with success and message keys.
 */
function addSeriesFromTMDB($tmdbId, $seasonsToInclude = [], $additionalServerTemplates = []) {
    $conn = getDBConnection();

    // Check for duplicates
    $stmt = $conn->prepare("SELECT id FROM content WHERE tmdb_id = :tmdb_id AND type = 'series'");
    $stmt->execute(['tmdb_id' => $tmdbId]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Series with this TMDB ID already exists.'];
    }

    $seriesData = fetchTMDB("/tv/{$tmdbId}");
    if (!$seriesData) {
        return ['success' => false, 'message' => 'Failed to fetch series data from TMDB.'];
    }

    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare(
            "INSERT INTO content (tmdb_id, type, title, description, poster_path, backdrop_path, release_date, year, runtime, rating, parental_rating, genres, country)
             VALUES (:tmdb_id, 'series', :title, :description, :poster_path, :backdrop_path, :release_date, :year, :runtime, :rating, :parental_rating, :genres, :country)"
        );

        $year = !empty($seriesData['first_air_date']) ? date('Y', strtotime($seriesData['first_air_date'])) : null;
        $genres = json_encode(array_column($seriesData['genres'] ?? [], 'name'));
        $runtime = $seriesData['episode_run_time'][0] ?? null;
        $country = $seriesData['origin_country'][0] ?? null;

        $stmt->execute([
            ':tmdb_id' => $tmdbId,
            ':title' => $seriesData['name'],
            ':description' => $seriesData['overview'],
            ':poster_path' => $seriesData['poster_path'],
            ':backdrop_path' => $seriesData['backdrop_path'],
            ':release_date' => $seriesData['first_air_date'],
            ':year' => $year,
            ':runtime' => $runtime,
            ':rating' => $seriesData['vote_average'],
            ':parental_rating' => getTVCertification($tmdbId),
            ':genres' => $genres,
            ':country' => $country
        ]);

        $contentId = $conn->lastInsertId();

        // If no specific seasons are requested, fetch all available seasons
        if (empty($seasonsToInclude)) {
            $seasonsToInclude = array_column($seriesData['seasons'], 'season_number');
        }

        foreach ($seasonsToInclude as $seasonNum) {
            $seasonData = fetchTMDB("/tv/{$tmdbId}/season/{$seasonNum}");
            if (!$seasonData) continue;

            $seasonStmt = $conn->prepare("INSERT INTO seasons (content_id, season_number, name, poster_path) VALUES (:content_id, :season_number, :name, :poster_path)");
            $seasonStmt->execute([
                ':content_id' => $contentId,
                ':season_number' => $seasonNum,
                ':name' => $seasonData['name'],
                ':poster_path' => $seasonData['poster_path']
            ]);
            $seasonId = $conn->lastInsertId();

            $episodeStmt = $conn->prepare("INSERT INTO episodes (season_id, episode_number, title, description, still_path, release_date) VALUES (:season_id, :episode_number, :title, :description, :still_path, :release_date)");
            $serverStmt = $conn->prepare("INSERT INTO servers (episode_id, name, url, quality) VALUES (:episode_id, :name, :url, :quality)");

            foreach ($seasonData['episodes'] as $episodeData) {
                $episodeStmt->execute([
                    ':season_id' => $seasonId,
                    ':episode_number' => $episodeData['episode_number'],
                    ':title' => $episodeData['name'],
                    ':description' => $episodeData['overview'],
                    ':still_path' => $episodeData['still_path'],
                    ':release_date' => $episodeData['air_date']
                ]);
                $episodeId = $conn->lastInsertId();

                // Add embed servers
                $embedSources = generateEmbedSources($tmdbId, 'tv', $seasonNum, $episodeData['episode_number']);
                foreach ($embedSources as $source) {
                    $serverStmt->execute([':episode_id' => $episodeId, ':name' => $source['name'], ':url' => $source['url'], ':quality' => $source['quality']]);
                }

                // Add additional servers from templates
                foreach ($additionalServerTemplates as $template) {
                    $url = str_replace(['{season}', '{episode}'], [$seasonNum, $episodeData['episode_number']], $template['urlTemplate']);
                    $serverStmt->execute([':episode_id' => $episodeId, ':name' => $template['name'], ':url' => $url, ':quality' => 'Auto']);
                }
            }
        }

        $conn->commit();
        return ['success' => true, 'message' => "Series '{$seriesData['name']}' added successfully."];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}


/**
 * Gets the US certification for a TV Show.
 * @param int $tmdbId The TMDB TV show ID.
 * @return string The certification or 'N/A'.
 */
function getTVCertification($tmdbId) {
    $data = fetchTMDB("/tv/{$tmdbId}/content_ratings");
    if ($data && isset($data['results'])) {
        foreach ($data['results'] as $result) {
            if ($result['iso_3166_1'] == 'US' && !empty($result['rating'])) {
                return $result['rating'];
            }
        }
    }
    return 'N/A';
}

/**
 * Gets the US certification for a movie.
 * @param int $tmdbId The TMDB movie ID.
 * @return string The certification or 'N/A'.
 */
function getMovieCertification($tmdbId) {
    $data = fetchTMDB("/movie/{$tmdbId}/release_dates");
    if ($data && isset($data['results'])) {
        foreach ($data['results'] as $result) {
            if ($result['iso_3166_1'] == 'US') {
                // Type 3 is Theatrical
                foreach ($result['release_dates'] as $release) {
                    if ($release['type'] == 3 && !empty($release['certification'])) {
                        return $release['certification'];
                    }
                }
            }
        }
    }
    return 'N/A';
}

/**
 * Generates an array of embed sources based on the configuration.
 * @param int $tmdbId
 * @param string $contentType 'movie' or 'tv'
 * @param int|null $seasonNum
 * @param int|null $episodeNum
 * @return array
 */
function generateEmbedSources($tmdbId, $contentType, $seasonNum = null, $episodeNum = null) {
    // This is a placeholder. In the final version, this would read from a config
    // or the admin panel settings stored in the database.
    $sources = [];

    $vidsrcUrl = "https://vidsrc.net/embed/{$contentType}/{$tmdbId}";
    if ($contentType === 'tv') {
        $vidsrcUrl .= "/{$seasonNum}/{$episodeNum}";
    }
    $sources[] = ['name' => 'VidSrc 1080p', 'url' => $vidsrcUrl, 'quality' => '1080p'];

    $vidjoyUrl = "https://vidjoy.pro/embed/{$contentType}/{$tmdbId}";
    if ($contentType === 'tv') {
        $vidjoyUrl .= "/{$seasonNum}/{$episodeNum}";
    }
    $sources[] = ['name' => 'VidJoy 1080p', 'url' => $vidjoyUrl, 'quality' => '1080p'];

    // Add other providers as needed...

    return $sources;
}
?>