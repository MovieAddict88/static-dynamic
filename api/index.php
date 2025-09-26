<?php
// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin

require_once '../config.php';

// --- API Router ---
$action = $_GET['action'] ?? 'browse'; // Default action is to browse content

try {
    $conn = getDBConnection();

    switch ($action) {
        case 'browse':
            $type = $_GET['type'] ?? 'all'; // 'movie', 'series', 'live', or 'all'
            $genre = $_GET['genre'] ?? 'all';
            $year = $_GET['year'] ?? 'all';
            $sort = $_GET['sort'] ?? 'newest';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

            $data = browseContent($conn, $type, $genre, $year, $sort, $page, $limit);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'details':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Content ID is required.');
            }
            $data = getContentDetails($conn, (int)$id);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'search':
            $query = $_GET['query'] ?? '';
            if (empty($query)) {
                throw new Exception('Search query is required.');
            }
            $data = searchContent($conn, $query);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'featured':
            // Fetch a few random, highly-rated items for the carousel
            $data = getFeaturedContent($conn);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'get_filters':
            $data = getFilterOptions($conn);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        default:
            throw new Exception('Invalid API action.');
    }
} catch (Exception $e) {
    // Return a JSON error response
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// --- API Functions ---

/**
 * Fetches a list of content based on filters.
 */
function browseContent($conn, $type, $genre, $year, $sort, $page, $limit) {
    $offset = ($page - 1) * $limit;

    // Base query
    $sql = "SELECT id, type, title, poster_path, year, rating, genres FROM content WHERE 1=1";
    $params = [];

    // Filtering
    if ($type !== 'all') {
        $sql .= " AND type = :type";
        $params[':type'] = $type;
    }
    if ($year !== 'all') {
        $sql .= " AND year = :year";
        $params[':year'] = $year;
    }
    if ($genre !== 'all') {
        // Use LIKE for JSON array stored as text
        $sql .= " AND genres LIKE :genre";
        $params[':genre'] = '%' . $genre . '%';
    }

    // Sorting
    switch ($sort) {
        case 'popular':
            $sql .= " ORDER BY rating DESC, year DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY rating DESC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY release_date DESC, id DESC";
            break;
    }

    // Get total count for pagination
    $totalStmt = $conn->prepare(str_replace("SELECT id, type, title, poster_path, year, rating, genres", "SELECT COUNT(*)", $sql));
    $totalStmt->execute($params);
    $totalResults = $totalStmt->fetchColumn();

    // Add pagination to query
    $sql .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => &$val) {
        // Bind integers as integers
        $dataType = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindParam($key, $val, $dataType);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'items' => $results,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => ceil($totalResults / $limit),
            'totalResults' => (int)$totalResults
        ]
    ];
}

/**
 * Fetches the full details for a single piece of content.
 */
function getContentDetails($conn, $id) {
    // Fetch main content
    $stmt = $conn->prepare("SELECT * FROM content WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$content) {
        return null;
    }

    // If it's a movie, get its servers
    if ($content['type'] === 'movie') {
        $serverStmt = $conn->prepare("SELECT name, url, quality FROM servers WHERE content_id = :id");
        $serverStmt->execute([':id' => $id]);
        $content['servers'] = $serverStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // If it's a series, get its seasons and episodes
    if ($content['type'] === 'series') {
        $seasonStmt = $conn->prepare("SELECT id, season_number, name, poster_path FROM seasons WHERE content_id = :id ORDER BY season_number ASC");
        $seasonStmt->execute([':id' => $id]);
        $seasons = $seasonStmt->fetchAll(PDO::FETCH_ASSOC);

        $episodeServerStmt = $conn->prepare("SELECT name, url, quality FROM servers WHERE episode_id = :episode_id");

        foreach ($seasons as &$season) {
            $episodeStmt = $conn->prepare("SELECT id, episode_number, title, description, still_path FROM episodes WHERE season_id = :season_id ORDER BY episode_number ASC");
            $episodeStmt->execute([':season_id' => $season['id']]);
            $episodes = $episodeStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($episodes as &$episode) {
                $episodeServerStmt->execute([':episode_id' => $episode['id']]);
                $episode['servers'] = $episodeServerStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $season['episodes'] = $episodes;
        }
        $content['seasons'] = $seasons;
    }

    return $content;
}

/**
 * Searches for content by title.
 */
function searchContent($conn, $query) {
    $sql = "SELECT id, type, title, poster_path, year, rating FROM content WHERE title LIKE :query LIMIT 20";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':query' => '%' . $query . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Gets featured content for the carousel.
 */
function getFeaturedContent($conn) {
    // Example: Get 5 most recent, highly-rated movies/series
    $sql = "SELECT id, type, title, description, backdrop_path, rating, year
            FROM content
            WHERE backdrop_path IS NOT NULL AND rating > 7.0
            ORDER BY release_date DESC
            LIMIT 5";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches unique filter options from the database.
 */
function getFilterOptions($conn) {
    // Get unique genres
    $genreStmt = $conn->query("SELECT DISTINCT genres FROM content WHERE genres IS NOT NULL AND genres != '[]'");
    $allGenres = [];
    while ($row = $genreStmt->fetch(PDO::FETCH_ASSOC)) {
        $genres = json_decode($row['genres'], true);
        if (is_array($genres)) {
            foreach ($genres as $genre) {
                if (!in_array($genre, $allGenres)) {
                    $allGenres[] = $genre;
                }
            }
        }
    }
    sort($allGenres);

    // Get unique years
    $yearStmt = $conn->query("SELECT DISTINCT year FROM content WHERE year IS NOT NULL ORDER BY year DESC");
    $years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

    // Get unique countries
    $countryStmt = $conn->query("SELECT DISTINCT country FROM content WHERE country IS NOT NULL AND country != '' ORDER BY country ASC");
    $countries = $countryStmt->fetchAll(PDO::FETCH_COLUMN);

    return [
        'genres' => $allGenres,
        'years' => $years,
        'countries' => $countries
    ];
}
?>