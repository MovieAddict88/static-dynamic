<?php
require_once 'auth.php';
require_once __DIR__ . '/../includes/config.php';

// Increase execution time and memory limit for large files
ini_set('max_execution_time', 600); // 10 minutes
ini_set('memory_limit', '1024M'); // 1GB

function redirectWithError($message) {
    header("Location: dashboard.php?import_status=error&message=" . urlencode($message));
    exit;
}

function redirectWithSuccess($message) {
    header("Location: dashboard.php?import_status=success&message=" . urlencode($message));
    exit;
}

if (isset($_POST['import'])) {
    if (isset($_FILES['jsonFile']) && $_FILES['jsonFile']['error'] == 0) {
        $fileTmpName = $_FILES['jsonFile']['tmp_name'];

        $jsonContent = file_get_contents($fileTmpName);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            redirectWithError("Error decoding JSON file: " . json_last_error_msg());
        }

        if (!isset($data['Categories']) || !is_array($data['Categories'])) {
            redirectWithError("Invalid JSON format. Expected a 'Categories' array.");
        }

        $conn->begin_transaction();

        try {
            // --- Optimization: Pre-fetch existing genres and countries into memory ---
            $genres_cache = [];
            $result_genres = $conn->query("SELECT id, name FROM genres");
            while ($row = $result_genres->fetch_assoc()) {
                $genres_cache[strtolower($row['name'])] = $row['id'];
            }

            $countries_cache = [];
            $result_countries = $conn->query("SELECT id, name FROM countries");
            while ($row = $result_countries->fetch_assoc()) {
                $countries_cache[strtolower($row['name'])] = $row['id'];
            }

            // Prepare statements
            $stmt_content = $conn->prepare("INSERT INTO content (title, description, poster, thumbnail, type, release_year, duration, rating, parental_rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_genre_insert = $conn->prepare("INSERT INTO genres (name) VALUES (?)");
            $stmt_country_insert = $conn->prepare("INSERT INTO countries (name) VALUES (?)");
            $stmt_content_genre = $conn->prepare("INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
            $stmt_content_country = $conn->prepare("INSERT INTO content_countries (content_id, country_id) VALUES (?, ?)");
            $stmt_season = $conn->prepare("INSERT INTO seasons (content_id, season_number, season_poster) VALUES (?, ?, ?)");
            $stmt_episode = $conn->prepare("INSERT INTO episodes (season_id, episode_number, title, description) VALUES (?, ?, ?, ?)");
            $stmt_server = $conn->prepare("INSERT INTO servers (content_id, episode_id, name, url) VALUES (?, ?, ?, ?)");

            $entries_processed = 0;

            foreach ($data['Categories'] as $category) {
                $contentType = '';
                if (stripos($category['MainCategory'], 'movie') !== false) $contentType = 'movie';
                elseif (stripos($category['MainCategory'], 'series') !== false) $contentType = 'series';
                elseif (stripos($category['MainCategory'], 'live') !== false) $contentType = 'live';
                else continue;

                foreach ($category['Entries'] as $entry) {
                    $stmt_content->bind_param("sssssisds", $entry['Title'], $entry['Description'], $entry['Poster'], $entry['Thumbnail'], $contentType, $entry['Year'], $entry['Duration'], $entry['Rating'], $entry['parentalRating']);
                    $stmt_content->execute();
                    $content_id = $conn->insert_id;

                    if (!empty($entry['SubCategory'])) {
                        $genre_name_lower = strtolower($entry['SubCategory']);
                        if (!isset($genres_cache[$genre_name_lower])) {
                            $stmt_genre_insert->bind_param("s", $entry['SubCategory']);
                            $stmt_genre_insert->execute();
                            $genre_id = $conn->insert_id;
                            $genres_cache[$genre_name_lower] = $genre_id;
                        } else {
                            $genre_id = $genres_cache[$genre_name_lower];
                        }
                        $stmt_content_genre->bind_param("ii", $content_id, $genre_id);
                        $stmt_content_genre->execute();
                    }

                    if (!empty($entry['Country'])) {
                        $country_name_lower = strtolower($entry['Country']);
                        if (!isset($countries_cache[$country_name_lower])) {
                            $stmt_country_insert->bind_param("s", $entry['Country']);
                            $stmt_country_insert->execute();
                            $country_id = $conn->insert_id;
                            $countries_cache[$country_name_lower] = $country_id;
                        } else {
                            $country_id = $countries_cache[$country_name_lower];
                        }
                        $stmt_content_country->bind_param("ii", $content_id, $country_id);
                        $stmt_content_country->execute();
                    }

                    if ($contentType !== 'series' && !empty($entry['Servers'])) {
                        foreach ($entry['Servers'] as $server) {
                            $episode_id_null = null;
                            $stmt_server->bind_param("iiss", $content_id, $episode_id_null, $server['name'], $server['url']);
                            $stmt_server->execute();
                        }
                    }

                    if ($contentType === 'series' && !empty($entry['Seasons'])) {
                        foreach ($entry['Seasons'] as $season) {
                            $stmt_season->bind_param("iis", $content_id, $season['Season'], $season['SeasonPoster']);
                            $stmt_season->execute();
                            $season_id = $conn->insert_id;

                            if (!empty($season['Episodes'])) {
                                foreach ($season['Episodes'] as $episode) {
                                    $stmt_episode->bind_param("iiss", $season_id, $episode['Episode'], $episode['Title'], $episode['Description']);
                                    $stmt_episode->execute();
                                    $episode_id = $conn->insert_id;

                                    if (!empty($episode['Servers'])) {
                                        foreach ($episode['Servers'] as $server) {
                                            $content_id_null = null;
                                            $stmt_server->bind_param("iiss", $content_id_null, $episode_id, $server['name'], $server['url']);
                                            $stmt_server->execute();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $entries_processed++;
                }
            }

            $conn->commit();
            redirectWithSuccess("Import successful. Processed {$entries_processed} entries.");

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            redirectWithError("Database transaction failed: " . $exception->getMessage());
        } finally {
            $conn->close();
        }
    } else {
        redirectWithError("File upload error or no file selected.");
    }
} else {
    redirectWithError("Invalid request.");
}
?>