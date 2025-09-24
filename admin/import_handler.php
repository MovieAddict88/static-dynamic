<?php
require_once '../config.php';

function handle_json_import($file) {
    $pdo = connect_db();
    if (!$pdo) {
        return "Error: Database connection failed.";
    }

    // File validation
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "File upload error. Code: " . $file['error'];
    }
    if ($file['type'] !== 'application/json') {
        return "Invalid file type. Please upload a .json file.";
    }

    $json_content = file_get_contents($file['tmp_name']);
    $data = json_decode($json_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Invalid JSON format: " . json_last_error_msg();
    }

    if (!isset($data['Categories']) || !is_array($data['Categories'])) {
        return "Invalid JSON structure. Missing 'Categories' array.";
    }

    $imported_count = 0;
    $skipped_count = 0;

    try {
        $pdo->beginTransaction();

        foreach ($data['Categories'] as $category) {
            $mainCategory = $category['MainCategory'] ?? 'Unknown';
            $type = '';
            if ($mainCategory === 'Movies') $type = 'movie';
            elseif ($mainCategory === 'TV Series') $type = 'series';
            elseif ($mainCategory === 'Live TV') $type = 'live';
            else continue; // Skip unknown categories

            foreach ($category['Entries'] as $entry) {
                // Check for duplicates by title and year
                $stmt = $pdo->prepare("SELECT id FROM content WHERE title = ? AND release_year = ?");
                $stmt->execute([$entry['Title'], $entry['Year'] ?? null]);
                if ($stmt->fetch()) {
                    $skipped_count++;
                    continue;
                }

                // Insert content
                $stmt = $pdo->prepare(
                    "INSERT INTO content (type, title, description, poster_url, thumbnail_url, release_year, rating, duration, parental_rating)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $type,
                    $entry['Title'],
                    $entry['Description'] ?? null,
                    $entry['Poster'] ?? null,
                    $entry['Thumbnail'] ?? null,
                    $entry['Year'] ?? null,
                    $entry['Rating'] ?? null,
                    $entry['Duration'] ?? null,
                    $entry['parentalRating'] ?? null
                ]);
                $contentId = $pdo->lastInsertId();

                // Insert servers for movies/live
                if (($type === 'movie' || $type === 'live') && !empty($entry['Servers'])) {
                    foreach ($entry['Servers'] as $server) {
                        $s_stmt = $pdo->prepare("INSERT INTO servers (content_id, name, url) VALUES (?, ?, ?)");
                        $s_stmt->execute([$contentId, $server['name'], $server['url']]);
                    }
                }

                // Insert seasons and episodes for series
                if ($type === 'series' && !empty($entry['Seasons'])) {
                    foreach ($entry['Seasons'] as $seasonData) {
                        $season_stmt = $pdo->prepare("INSERT INTO seasons (content_id, season_number, title, poster_url) VALUES (?, ?, ?, ?)");
                        $season_stmt->execute([$contentId, $seasonData['Season'], $seasonData['title'] ?? "Season {$seasonData['Season']}", $seasonData['SeasonPoster'] ?? null]);
                        $seasonId = $pdo->lastInsertId();

                        if (!empty($seasonData['Episodes'])) {
                            foreach ($seasonData['Episodes'] as $episodeData) {
                                $ep_stmt = $pdo->prepare("INSERT INTO episodes (season_id, episode_number, title, description, thumbnail_url, duration) VALUES (?, ?, ?, ?, ?, ?)");
                                $ep_stmt->execute([$seasonId, $episodeData['Episode'], $episodeData['Title'], $episodeData['Description'] ?? null, $episodeData['Thumbnail'] ?? null, $episodeData['Duration'] ?? null]);
                                $episodeId = $pdo->lastInsertId();

                                if (!empty($episodeData['Servers'])) {
                                    foreach ($episodeData['Servers'] as $server) {
                                        $s_stmt = $pdo->prepare("INSERT INTO servers (episode_id, name, url) VALUES (?, ?, ?)");
                                        $s_stmt->execute([$episodeId, $server['name'], $server['url']]);
                                    }
                                }
                            }
                        }
                    }
                }
                $imported_count++;
            }
        }

        $pdo->commit();
        return "Success: Imported {$imported_count} new items. Skipped {$skipped_count} duplicates.";

    } catch (Exception $e) {
        $pdo->rollBack();
        return "Database error during import: " . $e->getMessage();
    }
}
?>
