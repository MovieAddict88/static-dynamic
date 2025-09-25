<?php
// Public API for CineCraze

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin

require_once __DIR__ . '/../includes/db.php';

// --- Helper Functions ---

function api_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// --- Main Logic ---

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_content':
        $type = $_GET['type'] ?? 'all'; // movie, series, live, all
        $genre = $_GET['genre'] ?? 'all';
        $year = $_GET['year'] ?? 'all';
        $sort = $_GET['sort'] ?? 'newest';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT c.* FROM content c";
        $where = [];
        $params = [];

        if ($type !== 'all') {
            $where[] = "c.type = ?";
            $params[] = $type;
        }

        // Note: Genre and Year filtering would require more complex joins.
        // This is a simplified version for now.

        $sql .= !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

        switch ($sort) {
            case 'popular':
                $sql .= " ORDER BY c.rating DESC";
                break;
            case 'rating':
                 $sql .= " ORDER BY c.rating DESC";
                break;
            default: // newest
                $sql .= " ORDER BY c.year DESC, c.id DESC";
                break;
        }

        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) FROM content c" . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_items = $count_stmt->fetchColumn();
        $total_pages = ceil($total_items / $limit);

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        // PDO requires integer type for LIMIT and OFFSET
        foreach($params as $key => $param){
            $stmt->bindValue($key+1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $content = $stmt->fetchAll();

        echo json_encode([
            'status' => 'success',
            'page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_items,
            'data' => $content
        ]);
        break;

    case 'get_details':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) api_error('Invalid content ID.');

        $stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
        $stmt->execute([$id]);
        $content = $stmt->fetch();

        if (!$content) api_error('Content not found.');

        if ($content['type'] === 'series') {
            $seasons_stmt = $pdo->prepare("SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number ASC");
            $seasons_stmt->execute([$id]);
            $seasons = $seasons_stmt->fetchAll();

            foreach ($seasons as $key => $season) {
                $episodes_stmt = $pdo->prepare("SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number ASC");
                $episodes_stmt->execute([$season['id']]);
                $episodes = $episodes_stmt->fetchAll();

                foreach ($episodes as $e_key => $episode) {
                    $servers_stmt = $pdo->prepare("SELECT name, url FROM servers WHERE episode_id = ?");
                    $servers_stmt->execute([$episode['id']]);
                    $episodes[$e_key]['servers'] = $servers_stmt->fetchAll();
                }
                $seasons[$key]['episodes'] = $episodes;
            }
            $content['seasons'] = $seasons;
        } else {
            // For movies and live TV
            $servers_stmt = $pdo->prepare("SELECT name, url FROM servers WHERE content_id = ?");
            $servers_stmt->execute([$id]);
            $content['servers'] = $servers_stmt->fetchAll();
        }

        echo json_encode(['status' => 'success', 'data' => $content]);
        break;

    case 'search':
        $query = $_GET['q'] ?? '';
        if (empty($query)) api_error('Search query is required.');

        $stmt = $pdo->prepare("SELECT id, title, type, poster, year FROM content WHERE title LIKE ? LIMIT 10");
        $stmt->execute(["%{$query}%"]);
        $results = $stmt->fetchAll();

        echo json_encode(['status' => 'success', 'data' => $results]);
        break;

    default:
        api_error('Invalid API action.');
        break;
}
?>