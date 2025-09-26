<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_stats':
        get_stats($mysqli);
        break;
    case 'clear_all':
        clear_all_data($mysqli);
        break;
    case 'remove_duplicates':
        remove_duplicates($mysqli);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

function get_stats($mysqli) {
    try {
        $movie_count_res = $mysqli->query("SELECT COUNT(*) as count FROM movies");
        $movie_count = $movie_count_res->fetch_assoc()['count'];

        $series_count_res = $mysqli->query("SELECT COUNT(*) as count FROM series");
        $series_count = $series_count_res->fetch_assoc()['count'];

        $livetv_count_res = $mysqli->query("SELECT COUNT(*) as count FROM livetv");
        $livetv_count = $livetv_count_res->fetch_assoc()['count'];

        echo json_encode([
            'success' => true,
            'movie_count' => $movie_count,
            'series_count' => $series_count,
            'channel_count' => $livetv_count,
            'total_count' => $movie_count + $series_count + $livetv_count
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch stats: ' . $e->getMessage()]);
    }
}

function clear_all_data($mysqli) {
    try {
        $mysqli->query("TRUNCATE TABLE movies");
        $mysqli->query("TRUNCATE TABLE series");
        $mysqli->query("TRUNCATE TABLE seasons");
        $mysqli->query("TRUNCATE TABLE episodes");
        $mysqli->query("TRUNCATE TABLE servers");
        $mysqli->query("TRUNCATE TABLE livetv");

        echo json_encode(['success' => true, 'message' => 'All data has been cleared.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to clear data: ' . $e->getMessage()]);
    }
}

function remove_duplicates($mysqli) {
    try {
        $removed_count = 0;

        // Remove duplicate movies
        $sql_movies = "DELETE t1 FROM movies t1
        INNER JOIN movies t2
        WHERE
            t1.id < t2.id AND
            t1.title = t2.title AND
            t1.year = t2.year;";
        $mysqli->query($sql_movies);
        $removed_count += $mysqli->affected_rows;

        // Remove duplicate series
        $sql_series = "DELETE t1 FROM series t1
        INNER JOIN series t2
        WHERE
            t1.id < t2.id AND
            t1.title = t2.title AND
            t1.year = t2.year;";
        $mysqli->query($sql_series);
        $removed_count += $mysqli->affected_rows;

        // You could add similar queries for seasons and episodes if needed

        echo json_encode(['success' => true, 'message' => "Removed $removed_count duplicate entries."]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to remove duplicates: ' . $e->getMessage()]);
    }
}

$mysqli->close();
?>