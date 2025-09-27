<?php
header('Content-Type: application/json');
require_once '../config.php';

$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$type_filter = $_GET['type'] ?? 'all';
$search_term = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
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
    $content_id = $row['id'];

    // Fetch all genres for the content
    $genres_sql = "SELECT g.name FROM genres g JOIN content_genres cg ON g.id = cg.genre_id WHERE cg.content_id = " . $content_id;
    $genres_result = mysqli_query($link, $genres_sql);
    $genres = [];
    while ($genre_row = mysqli_fetch_assoc($genres_result)) {
        $genres[] = $genre_row['name'];
    }
    $row['genres'] = $genres;

    // Fetch servers for the content
    $servers_sql = "SELECT name, url FROM servers WHERE content_id = " . $content_id;
    $servers_result = mysqli_query($link, $servers_sql);
    $servers = [];
    while ($server_row = mysqli_fetch_assoc($servers_result)) {
        $servers[] = $server_row;
    }
    $row['servers'] = $servers;

    // Fetch seasons and episodes for series
    if ($row['type'] === 'series') {
        $seasons_sql = "SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number";
        $seasons_stmt = mysqli_prepare($link, $seasons_sql);
        mysqli_stmt_bind_param($seasons_stmt, "i", $content_id);
        mysqli_stmt_execute($seasons_stmt);
        $seasons_result = mysqli_stmt_get_result($seasons_stmt);
        $seasons = [];
        while ($season_row = mysqli_fetch_assoc($seasons_result)) {
            $season_id = $season_row['id'];
            $episodes_sql = "SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number";
            $episodes_stmt = mysqli_prepare($link, $episodes_sql);
            mysqli_stmt_bind_param($episodes_stmt, "i", $season_id);
            mysqli_stmt_execute($episodes_stmt);
            $episodes_result = mysqli_stmt_get_result($episodes_stmt);
            $episodes = [];
            while ($episode_row = mysqli_fetch_assoc($episodes_result)) {
                $episode_id = $episode_row['id'];
                $episode_servers_sql = "SELECT name, url FROM servers WHERE episode_id = " . $episode_id;
                $episode_servers_result = mysqli_query($link, $episode_servers_sql);
                $episode_servers = [];
                while ($episode_server_row = mysqli_fetch_assoc($episode_servers_result)) {
                    $episode_servers[] = $episode_server_row;
                }
                $episode_row['servers'] = $episode_servers;
                $episodes[] = $episode_row;
            }
            $season_row['episodes'] = $episodes;
            $seasons[] = $season_row;
        }
        $row['seasons'] = $seasons;
    }

    $content[] = $row;
}

$response = [
    'success' => true,
    'content' => $content,
    'pagination' => [
        'page' => $page,
        'total_pages' => $total_pages,
        'total_items' => $total_rows
    ]
];

// Reformat to match original playlist.json structure
$categories = [];
foreach ($response['content'] as $item) {
    $main_category = '';
    if ($item['type'] === 'movie') $main_category = 'Movies';
    if ($item['type'] === 'series') $main_category = 'TV Series';
    if ($item['type'] === 'live') $main_category = 'Live TV';

    if (!isset($categories[$main_category])) {
        $categories[$main_category] = ['MainCategory' => $main_category, 'SubCategories' => [], 'Entries' => []];
    }

    $entry = [
        'Title' => $item['title'],
        'SubCategory' => $item['genres'][0] ?? 'General',
        'Country' => $item['country'],
        'Description' => $item['description'],
        'Poster' => $item['poster'],
        'Thumbnail' => $item['thumbnail'],
        'Rating' => $item['rating'],
        'Duration' => $item['duration'],
        'Year' => $item['year'],
        'parentalRating' => $item['parental_rating'],
        'Servers' => $item['servers']
    ];

    if ($item['type'] === 'series') {
        $entry['Seasons'] = [];
        foreach ($item['seasons'] as $season) {
            $season_entry = [
                'Season' => $season['season_number'],
                'SeasonPoster' => $season['poster'],
                'Episodes' => []
            ];
            foreach ($season['episodes'] as $episode) {
                $season_entry['Episodes'][] = [
                    'Episode' => $episode['episode_number'],
                    'Title' => $episode['title'],
                    'Duration' => $episode['duration'],
                    'Description' => $episode['description'],
                    'Thumbnail' => $episode['thumbnail'],
                    'Servers' => $episode['servers']
                ];
            }
            $entry['Seasons'][] = $season_entry;
        }
    }

    $categories[$main_category]['Entries'][] = $entry;
    if (!in_array($entry['SubCategory'], $categories[$main_category]['SubCategories'])) {
        $categories[$main_category]['SubCategories'][] = $entry['SubCategory'];
    }
}

$final_response = ['Categories' => array_values($categories)];

echo json_encode($final_response, JSON_PRETTY_PRINT);

mysqli_close($link);
?>