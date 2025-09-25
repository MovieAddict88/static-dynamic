<?php
require_once 'auth.php';
require_once __DIR__ . '/../includes/db.php';

// This file will handle various data management tasks
// like importing, exporting, and clearing data.

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Invalid action.'];

// We need to set the header before any output, so we'll do it here
if ($action === 'export_json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="playlist_export_' . date('Y-m-d') . '.json"');
} else {
    header('Content-Type: application/json');
}


switch ($action) {
    case 'get_stats':
        try {
            $stmt_movies = $pdo->query("SELECT COUNT(*) FROM content WHERE type = 'movie'");
            $movie_count = $stmt_movies->fetchColumn();

            $stmt_series = $pdo->query("SELECT COUNT(*) FROM content WHERE type = 'series'");
            $series_count = $stmt_series->fetchColumn();

            $stmt_live = $pdo->query("SELECT COUNT(*) FROM content WHERE type = 'live'");
            $live_count = $stmt_live->fetchColumn();

            $response = [
                'status' => 'success',
                'data' => [
                    'movies' => $movie_count,
                    'series' => $series_count,
                    'live' => $live_count,
                    'total' => $movie_count + $series_count + $live_count
                ]
            ];
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
        break;

    case 'import_json':
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
            $json_file = $_FILES['import_file']['tmp_name'];
            $json_data = file_get_contents($json_file);
            $data = json_decode($json_data, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($data['Categories'])) {
                try {
                    $pdo->beginTransaction();

                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
                    $pdo->exec("TRUNCATE TABLE servers;");
                    $pdo->exec("TRUNCATE TABLE episodes;");
                    $pdo->exec("TRUNCATE TABLE seasons;");
                    $pdo->exec("TRUNCATE TABLE content_genres;");
                    $pdo->exec("TRUNCATE TABLE genres;");
                    $pdo->exec("TRUNCATE TABLE content;");
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

                    $genre_stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
                    $content_stmt = $pdo->prepare("INSERT INTO content (tmdb_id, type, title, description, poster, thumbnail, year, duration, rating, parental_rating, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $content_genre_stmt = $pdo->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                    $season_stmt = $pdo->prepare("INSERT INTO seasons (content_id, season_number, name, poster) VALUES (?, ?, ?, ?)");
                    $episode_stmt = $pdo->prepare("INSERT INTO episodes (season_id, episode_number, title, description, thumbnail, duration) VALUES (?, ?, ?, ?, ?, ?)");
                    $server_stmt = $pdo->prepare("INSERT INTO servers (content_id, episode_id, name, url) VALUES (?, ?, ?, ?)");

                    foreach ($data['Categories'] as $category) {
                        $type = 'live';
                        if ($category['MainCategory'] === 'Movies') $type = 'movie';
                        if ($category['MainCategory'] === 'TV Series') $type = 'series';

                        foreach ($category['Entries'] as $entry) {
                            $content_stmt->execute([
                                $entry['tmdb_id'] ?? null, $type, $entry['Title'], $entry['Description'] ?? null,
                                $entry['Poster'] ?? null, $entry['Thumbnail'] ?? null, $entry['Year'] ?? null,
                                $entry['Duration'] ?? null, $entry['Rating'] ?? null, $entry['parentalRating'] ?? null,
                                $entry['Country'] ?? null
                            ]);
                            $content_id = $pdo->lastInsertId();

                            if (!empty($entry['SubCategory'])) {
                                $genre_stmt->execute([$entry['SubCategory']]);
                                $genre_id = $pdo->lastInsertId();
                                if ($genre_id > 0) {
                                   $content_genre_stmt->execute([$content_id, $genre_id]);
                                }
                            }

                            if (($type === 'movie' || $type === 'live') && isset($entry['Servers'])) {
                                foreach ($entry['Servers'] as $server) {
                                    $server_stmt->execute([$content_id, null, $server['name'], $server['url']]);
                                }
                            } elseif ($type === 'series' && isset($entry['Seasons'])) {
                                foreach ($entry['Seasons'] as $season_data) {
                                    $season_stmt->execute([$content_id, $season_data['Season'], "Season " . $season_data['Season'], $season_data['SeasonPoster'] ?? null]);
                                    $season_id = $pdo->lastInsertId();

                                    if (isset($season_data['Episodes'])) {
                                        foreach ($season_data['Episodes'] as $episode_data) {
                                            $episode_stmt->execute([$season_id, $episode_data['Episode'], $episode_data['Title'], $episode_data['Description'] ?? null, $episode_data['Thumbnail'] ?? null, $episode_data['Duration'] ?? null]);
                                            $episode_id = $pdo->lastInsertId();

                                            if (isset($episode_data['Servers'])) {
                                                foreach ($episode_data['Servers'] as $server) {
                                                    $server_stmt->execute([null, $episode_id, $server['name'], $server['url']]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $pdo->commit();
                    $response = ['status' => 'success', 'message' => 'JSON data imported successfully!'];
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $response['message'] = 'Import failed: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Invalid JSON file or format. Expected a "Categories" array. JSON error: ' . json_last_error_msg();
            }
        } else {
            $response['message'] = 'File upload error: ' . ($_FILES['import_file']['error'] ?? 'Unknown error');
        }
        break;

    case 'export_json':
        try {
            $export_data = ['Categories' => []];

            $types_map = ['movie' => 'Movies', 'series' => 'TV Series', 'live' => 'Live TV'];

            foreach ($types_map as $type => $mainCategoryName) {
                $category_data = [
                    'MainCategory' => $mainCategoryName,
                    'SubCategories' => [],
                    'Entries' => []
                ];

                // Fetch all content of a specific type
                $content_stmt = $pdo->prepare("SELECT * FROM content WHERE type = ?");
                $content_stmt->execute([$type]);
                $contents = $content_stmt->fetchAll();

                $subCategories = [];

                foreach ($contents as $content) {
                    $entry = [
                        'Title' => $content['title'],
                        'Description' => $content['description'],
                        'Poster' => $content['poster'],
                        'Thumbnail' => $content['thumbnail'],
                        'Rating' => $content['rating'],
                        'Duration' => $content['duration'],
                        'Year' => $content['year'],
                        'parentalRating' => $content['parental_rating'],
                        'Country' => $content['country'],
                        'tmdb_id' => $content['tmdb_id'],
                    ];

                    // Get genre
                    $genre_stmt = $pdo->prepare("SELECT g.name FROM genres g JOIN content_genres cg ON g.id = cg.genre_id WHERE cg.content_id = ?");
                    $genre_stmt->execute([$content['id']]);
                    $genre = $genre_stmt->fetchColumn();
                    if ($genre) {
                        $entry['SubCategory'] = $genre;
                        if (!in_array($genre, $subCategories)) {
                            $subCategories[] = $genre;
                        }
                    }

                    if ($type === 'series') {
                        $entry['Seasons'] = [];
                        $seasons_stmt = $pdo->prepare("SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number ASC");
                        $seasons_stmt->execute([$content['id']]);
                        $seasons = $seasons_stmt->fetchAll();

                        foreach ($seasons as $season) {
                            $season_data = [
                                'Season' => $season['season_number'],
                                'SeasonPoster' => $season['poster'],
                                'Episodes' => []
                            ];

                            $episodes_stmt = $pdo->prepare("SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number ASC");
                            $episodes_stmt->execute([$season['id']]);
                            $episodes = $episodes_stmt->fetchAll();

                            foreach ($episodes as $episode) {
                                $servers_stmt = $pdo->prepare("SELECT name, url FROM servers WHERE episode_id = ?");
                                $servers_stmt->execute([$episode['id']]);
                                $servers = $servers_stmt->fetchAll();

                                $season_data['Episodes'][] = [
                                    'Episode' => $episode['episode_number'],
                                    'Title' => $episode['title'],
                                    'Description' => $episode['description'],
                                    'Thumbnail' => $episode['thumbnail'],
                                    'Duration' => $episode['duration'],
                                    'Servers' => $servers
                                ];
                            }
                            $entry['Seasons'][] = $season_data;
                        }
                    } else {
                        $servers_stmt = $pdo->prepare("SELECT name, url FROM servers WHERE content_id = ?");
                        $servers_stmt->execute([$content['id']]);
                        $entry['Servers'] = $servers_stmt->fetchAll();
                    }
                    $category_data['Entries'][] = $entry;
                }
                $category_data['SubCategories'] = $subCategories;
                $export_data['Categories'][] = $category_data;
            }

            echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;

        } catch (PDOException $e) {
            api_error('Export failed: ' . $e->getMessage());
        }
        break;

    case 'clear_data':
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("TRUNCATE TABLE servers;");
            $pdo->exec("TRUNCATE TABLE episodes;");
            $pdo->exec("TRUNCATE TABLE seasons;");
            $pdo->exec("TRUNCATE TABLE content_genres;");
            $pdo->exec("TRUNCATE TABLE genres;");
            $pdo->exec("TRUNCATE TABLE content;");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $response = ['status' => 'success', 'message' => 'All content data has been cleared.'];
        } catch (PDOException $e) {
            $response['message'] = 'Failed to clear data: ' . $e->getMessage();
        }
        break;
}

echo json_encode($response);
?>