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

/**
 * Retrieves a paginated and filtered list of all content from the database.
 *
 * @param int $page The current page number.
 * @param int $limit The number of items per page.
 * @param string $filter The content type to filter by ('all', 'movie', 'series', 'live').
 * @param string $search A search term to filter by title.
 * @return array An array containing the content items and pagination info.
 */
function getAllContent($page, $limit, $filter, $search) {
    $conn = getDBConnection();
    $offset = ($page - 1) * $limit;

    // Base query
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM content WHERE 1=1";
    $params = [];

    // Filtering
    if ($filter !== 'all') {
        $sql .= " AND type = :type";
        $params[':type'] = $filter;
    }
    if (!empty($search)) {
        $sql .= " AND title LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    // Ordering and Pagination
    $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);

    // Bind parameters, ensuring correct types
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    if ($filter !== 'all') {
        $stmt->bindValue(':type', $params[':type'], PDO::PARAM_STR);
    }
    if (!empty($search)) {
        $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of rows for pagination
    $totalResults = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();

    return [
        'success' => true,
        'data' => $results,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => ceil($totalResults / $limit),
            'totalResults' => (int)$totalResults
        ]
    ];
}

/**
 * Deletes content and all its related data (servers, seasons, episodes) by its ID.
 *
 * @param int $contentId The ID of the content to delete.
 * @return array A status array with success and message keys.
 */
function deleteContentById($contentId) {
    $conn = getDBConnection();
    $conn->beginTransaction();

    try {
        // Find content type first
        $stmt = $conn->prepare("SELECT type FROM content WHERE id = :id");
        $stmt->execute([':id' => $contentId]);
        $content = $stmt->fetch();

        if (!$content) {
            $conn->rollBack();
            return ['success' => false, 'message' => 'Content not found.'];
        }

        if ($content['type'] === 'movie') {
            // Delete servers for the movie
            $stmt = $conn->prepare("DELETE FROM servers WHERE content_id = :content_id");
            $stmt->execute([':content_id' => $contentId]);
        } elseif ($content['type'] === 'series') {
            // Get season IDs for the series
            $stmt = $conn->prepare("SELECT id FROM seasons WHERE content_id = :content_id");
            $stmt->execute([':content_id' => $contentId]);
            $seasonIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($seasonIds) {
                // Get episode IDs for all seasons
                $inSeasons = str_repeat('?,', count($seasonIds) - 1) . '?';
                $stmt = $conn->prepare("SELECT id FROM episodes WHERE season_id IN ($inSeasons)");
                $stmt->execute($seasonIds);
                $episodeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if ($episodeIds) {
                    // Delete servers for all episodes
                    $inEpisodes = str_repeat('?,', count($episodeIds) - 1) . '?';
                    $stmt = $conn->prepare("DELETE FROM servers WHERE episode_id IN ($inEpisodes)");
                    $stmt->execute($episodeIds);
                }

                // Delete episodes for all seasons
                $stmt = $conn->prepare("DELETE FROM episodes WHERE season_id IN ($inSeasons)");
                $stmt->execute($seasonIds);
            }

            // Delete seasons
            $stmt = $conn->prepare("DELETE FROM seasons WHERE content_id = :content_id");
            $stmt->execute([':content_id' => $contentId]);
        }

        // Finally, delete the content itself
        $stmt = $conn->prepare("DELETE FROM content WHERE id = :id");
        $stmt->execute([':id' => $contentId]);

        $conn->commit();
        return ['success' => true, 'message' => 'Content deleted successfully.'];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Updates an existing content item in the database.
 *
 * @param array $data The content data to update. Must include an 'id'.
 * @return array A status array with success and message keys.
 */
function updateContent($data) {
    $conn = getDBConnection();

    // Fields that can be updated in the 'content' table
    $allowedFields = ['title', 'description', 'poster_path', 'backdrop_path', 'release_date', 'year', 'runtime', 'rating', 'parental_rating', 'genres', 'country'];

    $setClauses = [];
    $params = [':id' => $data['id']];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $data[$field];
        }
    }

    if (empty($setClauses)) {
        return ['success' => false, 'message' => 'No valid fields provided for update.'];
    }

    try {
        $sql = "UPDATE content SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Optionally, handle server/episode updates here if needed
        // For now, we only update the main content table.

        return ['success' => true, 'message' => 'Content updated successfully.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Imports content from a JSON data structure into the database.
 *
 * @param array $data The decoded JSON data.
 * @return array A status array with success and message keys.
 */
function importDataFromJson($data) {
    $conn = getDBConnection();
    $conn->beginTransaction();

    $stats = ['movies' => 0, 'series' => 0, 'skipped' => 0, 'failed' => 0];
    $errors = [];

    try {
        // Prepare statements for reuse
        $contentStmt = $conn->prepare(
            "INSERT INTO content (tmdb_id, type, title, description, poster_path, backdrop_path, release_date, year, runtime, rating, parental_rating, genres, country)
             VALUES (:tmdb_id, :type, :title, :description, :poster_path, :backdrop_path, :release_date, :year, :runtime, :rating, :parental_rating, :genres, :country)"
        );
        $movieServerStmt = $conn->prepare("INSERT INTO servers (content_id, name, url, quality) VALUES (:content_id, :name, :url, :quality)");
        $seasonStmt = $conn->prepare("INSERT INTO seasons (content_id, season_number, name, poster_path) VALUES (:content_id, :season_number, :name, :poster_path)");
        $episodeStmt = $conn->prepare("INSERT INTO episodes (season_id, episode_number, title, description, still_path, release_date) VALUES (:season_id, :episode_number, :title, :description, :still_path, :release_date)");
        $episodeServerStmt = $conn->prepare("INSERT INTO servers (episode_id, name, url, quality) VALUES (:episode_id, :name, :url, :quality)");

        foreach ($data as $item) {
            // Basic validation
            if (empty($item['type']) || empty($item['title'])) {
                $stats['failed']++;
                $errors[] = "Skipping item due to missing type or title.";
                continue;
            }

            // Check for duplicates if tmdb_id is provided
            if (!empty($item['tmdb_id'])) {
                $stmt = $conn->prepare("SELECT id FROM content WHERE tmdb_id = :tmdb_id AND type = :type");
                $stmt->execute([':tmdb_id' => $item['tmdb_id'], ':type' => $item['type']]);
                if ($stmt->fetch()) {
                    $stats['skipped']++;
                    continue; // Skip existing item
                }
            }

            // Insert into content table
            $contentStmt->execute([
                ':tmdb_id' => $item['tmdb_id'] ?? null,
                ':type' => $item['type'],
                ':title' => $item['title'],
                ':description' => $item['description'] ?? null,
                ':poster_path' => $item['poster_path'] ?? null,
                ':backdrop_path' => $item['backdrop_path'] ?? null,
                ':release_date' => !empty($item['release_date']) ? $item['release_date'] : null,
                ':year' => $item['year'] ?? null,
                ':runtime' => $item['runtime'] ?? null,
                ':rating' => $item['rating'] ?? null,
                ':parental_rating' => $item['parental_rating'] ?? null,
                ':genres' => isset($item['genres']) ? json_encode($item['genres']) : null,
                ':country' => $item['country'] ?? null
            ]);
            $contentId = $conn->lastInsertId();

            if ($item['type'] === 'movie') {
                $stats['movies']++;
                if (!empty($item['servers']) && is_array($item['servers'])) {
                    foreach ($item['servers'] as $server) {
                        $movieServerStmt->execute([
                            ':content_id' => $contentId,
                            ':name' => $server['name'] ?? 'Server',
                            ':url' => $server['url'] ?? '',
                            ':quality' => $server['quality'] ?? 'Auto'
                        ]);
                    }
                }
            } elseif ($item['type'] === 'series') {
                $stats['series']++;
                if (!empty($item['seasons']) && is_array($item['seasons'])) {
                    foreach ($item['seasons'] as $season) {
                        $seasonStmt->execute([
                            ':content_id' => $contentId,
                            ':season_number' => $season['season_number'],
                            ':name' => $season['name'] ?? "Season {$season['season_number']}",
                            ':poster_path' => $season['poster_path'] ?? null
                        ]);
                        $seasonId = $conn->lastInsertId();

                        if (!empty($season['episodes']) && is_array($season['episodes'])) {
                            foreach ($season['episodes'] as $episode) {
                                $episodeStmt->execute([
                                    ':season_id' => $seasonId,
                                    ':episode_number' => $episode['episode_number'],
                                    ':title' => $episode['title'] ?? "Episode {$episode['episode_number']}",
                                    ':description' => $episode['description'] ?? null,
                                    ':still_path' => $episode['still_path'] ?? null,
                                    ':release_date' => !empty($episode['release_date']) ? $episode['release_date'] : null
                                ]);
                                $episodeId = $conn->lastInsertId();

                                if (!empty($episode['servers']) && is_array($episode['servers'])) {
                                    foreach ($episode['servers'] as $server) {
                                        $episodeServerStmt->execute([
                                            ':episode_id' => $episodeId,
                                            ':name' => $server['name'] ?? 'Server',
                                            ':url' => $server['url'] ?? '',
                                            ':quality' => $server['quality'] ?? 'Auto'
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $conn->commit();
        $message = "Import successful. Added: {$stats['movies']} movies, {$stats['series']} series. Skipped {$stats['skipped']} duplicates.";
        if ($stats['failed'] > 0) {
            $message .= " Failed to import {$stats['failed']} items.";
        }
        return ['success' => true, 'message' => $message, 'details' => $errors];

    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Database error during import: ' . $e->getMessage()];
    }
}

/**
 * Retrieves full details for a single content item, including servers, seasons, and episodes.
 *
 * @param int $contentId The ID of the content to retrieve.
 * @return array A status array with the detailed content data.
 */
function getContentDetails($contentId) {
    $conn = getDBConnection();

    try {
        // Get the main content data
        $stmt = $conn->prepare("SELECT * FROM content WHERE id = :id");
        $stmt->execute([':id' => $contentId]);
        $content = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$content) {
            return ['success' => false, 'message' => 'Content not found.'];
        }

        // Decode JSON fields
        $content['genres'] = json_decode($content['genres'], true);

        if ($content['type'] === 'movie' || $content['type'] === 'live') {
            // Fetch servers for the movie or live stream
            $serverStmt = $conn->prepare("SELECT name, url, quality FROM servers WHERE content_id = :content_id");
            $serverStmt->execute([':content_id' => $contentId]);
            $content['servers'] = $serverStmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($content['type'] === 'series') {
            // Fetch seasons for the series
            $seasonStmt = $conn->prepare("SELECT id, season_number, name, poster_path FROM seasons WHERE content_id = :content_id ORDER BY season_number ASC");
            $seasonStmt->execute([':content_id' => $contentId]);
            $seasons = $seasonStmt->fetchAll(PDO::FETCH_ASSOC);

            // Prepare statements for episodes and servers to reuse in the loop
            $episodeStmt = $conn->prepare("SELECT id, episode_number, title, description, still_path, release_date FROM episodes WHERE season_id = :season_id ORDER BY episode_number ASC");
            $serverStmt = $conn->prepare("SELECT name, url, quality FROM servers WHERE episode_id = :episode_id");

            // For each season, fetch its episodes and their respective servers
            foreach ($seasons as &$season) { // Use reference to modify the array directly
                $episodeStmt->execute([':season_id' => $season['id']]);
                $episodes = $episodeStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($episodes as &$episode) { // Use reference
                    $serverStmt->execute([':episode_id' => $episode['id']]);
                    $episode['servers'] = $serverStmt->fetchAll(PDO::FETCH_ASSOC);
                }
                $season['episodes'] = $episodes;
            }
            $content['seasons'] = $seasons;
        }

        return ['success' => true, 'data' => $content];

    } catch (Exception $e) {
        // In a real application, you would log the error instead of exposing it.
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>