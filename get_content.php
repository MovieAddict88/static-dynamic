<?php
require_once 'check_login.php';
require_once 'config.php';

$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$type_filter = $_GET['type'] ?? 'all';
$search_term = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$sql = "SELECT c.*, g.name as genre_name FROM content c LEFT JOIN content_genres cg ON c.id = cg.content_id LEFT JOIN genres g ON cg.genre_id = g.id WHERE 1=1";

if ($type_filter !== 'all') {
    $sql .= " AND c.type = '" . mysqli_real_escape_string($link, $type_filter) . "'";
}

if (!empty($search_term)) {
    $sql .= " AND c.title LIKE '%" . mysqli_real_escape_string($link, $search_term) . "%'";
}

$sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

$count_result = mysqli_query($link, str_replace("c.*, g.name as genre_name", "COUNT(DISTINCT c.id) as total", $sql));
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

$sql .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($link, $sql);

$content = [];
while ($row = mysqli_fetch_assoc($result)) {
    $content[] = $row;
}

echo json_encode([
    'success' => true,
    'content' => $content,
    'pagination' => [
        'page' => $page,
        'total_pages' => $total_pages,
        'total_items' => $total_rows
    ]
]);

mysqli_close($link);
?>