<?php
require_once 'check_login.php';
require_once 'config.php';

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid content ID.']);
    exit;
}

$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Fetch content details
$sql = "SELECT * FROM content WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$content = mysqli_fetch_assoc($result);

if (!$content) {
    echo json_encode(['success' => false, 'message' => 'Content not found.']);
    exit;
}

// Fetch genres
$sql = "SELECT g.id, g.name FROM genres g JOIN content_genres cg ON g.id = cg.genre_id WHERE cg.content_id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$genres = [];
while ($row = mysqli_fetch_assoc($result)) {
    $genres[] = $row;
}
$content['genres'] = $genres;

// Fetch servers
$sql = "SELECT * FROM servers WHERE content_id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$servers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $servers[] = $row;
}
$content['servers'] = $servers;

// Fetch seasons and episodes for series
if ($content['type'] === 'series') {
    $sql = "SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $seasons = [];
    while ($season_row = mysqli_fetch_assoc($result)) {
        $season_id = $season_row['id'];
        $episodes_sql = "SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number";
        $episodes_stmt = mysqli_prepare($link, $episodes_sql);
        mysqli_stmt_bind_param($episodes_stmt, "i", $season_id);
        mysqli_stmt_execute($episodes_stmt);
        $episodes_result = mysqli_stmt_get_result($episodes_stmt);
        $episodes = [];
        while ($episode_row = mysqli_fetch_assoc($episodes_result)) {
            $episodes[] = $episode_row;
        }
        $season_row['episodes'] = $episodes;
        $seasons[] = $season_row;
    }
    $content['seasons'] = $seasons;
}

echo json_encode(['success' => true, 'content' => $content]);

mysqli_close($link);
?>