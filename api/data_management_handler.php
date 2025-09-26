<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$pdo = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_stats':
        get_stats($pdo);
        break;
    case 'clear_all':
        clear_all_data($pdo);
        break;
    case 'remove_duplicates':
        remove_duplicates($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

function get_stats($pdo) {
    try {
        $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM content GROUP BY type");
        $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $movie_count = $counts['movie'] ?? 0;
        $series_count = $counts['series'] ?? 0;
        $channel_count = $counts['live'] ?? 0;

        echo json_encode([
            'success' => true,
            'movie_count' => $movie_count,
            'series_count' => $series_count,
            'channel_count' => $channel_count,
            'total_count' => $movie_count + $series_count + $channel_count
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch stats: ' . $e->getMessage()]);
    }
}

function clear_all_data($pdo) {
    try {
        // Order is important due to foreign key constraints
        $pdo->query("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->query("TRUNCATE TABLE content_genres");
        $pdo->query("TRUNCATE TABLE content_countries");
        $pdo->query("TRUNCATE TABLE servers");
        $pdo->query("TRUNCATE TABLE episodes");
        $pdo->query("TRUNCATE TABLE seasons");
        $pdo->query("TRUNCATE TABLE content");
        $pdo->query("TRUNCATE TABLE genres");
        $pdo->query("TRUNCATE TABLE countries");
        $pdo->query("SET FOREIGN_KEY_CHECKS = 1");

        echo json_encode(['success' => true, 'message' => 'All data has been cleared successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to clear data: ' . $e->getMessage()]);
    }
}

function remove_duplicates($pdo) {
    try {
        $sql = "DELETE c1 FROM content c1
                INNER JOIN content c2
                WHERE
                    c1.id < c2.id AND
                    c1.title = c2.title AND
                    c1.type = c2.type AND
                    (c1.year = c2.year OR c1.year IS NULL AND c2.year IS NULL);";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $removed_count = $stmt->rowCount();

        echo json_encode(['success' => true, 'message' => "Removed $removed_count duplicate entries."]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to remove duplicates: ' . $e->getMessage()]);
    }
}
?>