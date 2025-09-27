<?php
require_once 'check_login.php';
require_once 'config.php';

$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    die("Database connection failed.");
}

$data = ['Categories' => []];
$categories = [];

// Fetch all content
$content_result = mysqli_query($link, "SELECT * FROM content");
while ($content = mysqli_fetch_assoc($content_result)) {
    $type = $content['type'];
    $main_category = '';
    if ($type === 'movie') $main_category = 'Movies';
    if ($type === 'series') $main_category = 'TV Series';
    if ($type === 'live') $main_category = 'Live TV';

    if (!isset($categories[$main_category])) {
        $categories[$main_category] = ['MainCategory' => $main_category, 'SubCategories' => [], 'Entries' => []];
    }

    $entry = [
        'Title' => $content['title'],
        'Description' => $content['description'],
        'Poster' => $content['poster'],
        'Thumbnail' => $content['thumbnail'],
        'Year' => $content['year'],
        'Rating' => $content['rating'],
        'parentalRating' => $content['parental_rating'],
        'Country' => $content['country'],
        'Servers' => []
    ];

    // Fetch genres for content
    $genre_sql = "SELECT g.name FROM genres g JOIN content_genres cg ON g.id = cg.genre_id WHERE cg.content_id = " . $content['id'];
    $genre_result = mysqli_query($link, $genre_sql);
    if ($genre_row = mysqli_fetch_assoc($genre_result)) {
        $entry['SubCategory'] = $genre_row['name'];
        if (!in_array($genre_row['name'], $categories[$main_category]['SubCategories'])) {
            $categories[$main_category]['SubCategories'][] = $genre_row['name'];
        }
    }

    // Fetch servers for content
    $server_sql = "SELECT name, url FROM servers WHERE content_id = " . $content['id'];
    $server_result = mysqli_query($link, $server_sql);
    while ($server = mysqli_fetch_assoc($server_result)) {
        $entry['Servers'][] = $server;
    }

    $categories[$main_category]['Entries'][] = $entry;
}

$data['Categories'] = array_values($categories);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="playlist.json"');
echo json_encode($data, JSON_PRETTY_PRINT);

mysqli_close($link);
?>