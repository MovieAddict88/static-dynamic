<?php
require_once 'check_login.php';
require_once 'config.php';
require_once 'utils.php'; // For get_or_create_genre

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['jsonFile']) && $_FILES['jsonFile']['error'] == 0) {
        $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if (!$link) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
            exit;
        }

        $json_string = file_get_contents($_FILES['jsonFile']['tmp_name']);
        $data = json_decode($json_string, true);

        if (isset($data['Categories']) && is_array($data['Categories'])) {
            foreach ($data['Categories'] as $category) {
                foreach ($category['Entries'] as $entry) {
                    $type = '';
                    if ($category['MainCategory'] === 'Movies') $type = 'movie';
                    if ($category['MainCategory'] === 'TV Series') $type = 'series';
                    if ($category['MainCategory'] === 'Live TV') $type = 'live';

                    $stmt = mysqli_prepare($link, "INSERT INTO content (title, description, poster, thumbnail, type, year, rating, parental_rating, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "sssssisss", $entry['Title'], $entry['Description'], $entry['Poster'], $entry['Thumbnail'], $type, $entry['Year'], $entry['Rating'], $entry['parentalRating'], $entry['Country']);
                    mysqli_stmt_execute($stmt);
                    $content_id = mysqli_insert_id($link);

                    $genre_id = get_or_create_genre($link, $entry['SubCategory']);
                    $content_genre_stmt = mysqli_prepare($link, "INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
                    mysqli_stmt_bind_param($content_genre_stmt, "ii", $content_id, $genre_id);
                    mysqli_stmt_execute($content_genre_stmt);

                    if (isset($entry['Servers'])) {
                        foreach ($entry['Servers'] as $server) {
                            $server_stmt = mysqli_prepare($link, "INSERT INTO servers (content_id, name, url) VALUES (?, ?, ?)");
                            mysqli_stmt_bind_param($server_stmt, "iss", $content_id, $server['name'], $server['url']);
                            mysqli_stmt_execute($server_stmt);
                        }
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Data imported successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON format.']);
        }
        mysqli_close($link);
    } else {
        echo json_encode(['success' => false, 'message' => 'File upload error.']);
    }
}
?>