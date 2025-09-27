<?php
require_once 'check_login.php';
require_once 'config.php';
require_once 'utils.php'; // For get_or_create_genre

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$link) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    $type = $_POST['type'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $poster = $_POST['poster'] ?? '';
    $year = $_POST['year'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $parental_rating = $_POST['parental_rating'] ?? '';
    $country = $_POST['country'] ?? '';
    $subcategory = $_POST['subcategory'] ?? '';

    $stmt = mysqli_prepare($link, "INSERT INTO content (title, description, poster, thumbnail, type, year, rating, parental_rating, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssisss", $title, $description, $poster, $poster, $type, $year, $rating, $parental_rating, $country);

    if (mysqli_stmt_execute($stmt)) {
        $content_id = mysqli_insert_id($link);

        // Handle genre
        $genre_id = get_or_create_genre($link, $subcategory);
        $content_genre_stmt = mysqli_prepare($link, "INSERT INTO content_genres (content_id, genre_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($content_genre_stmt, "ii", $content_id, $genre_id);
        mysqli_stmt_execute($content_genre_stmt);

        // Handle servers
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

        echo json_encode(['success' => true, 'message' => 'Content added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert content.']);
    }

    mysqli_close($link);
}
?>