<?php
header('Content-Type: application/json');
require_once 'tmdb_handler.php'; // Re-use the fetchTMDB function

// Simple router for API actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'search_tmdb':
        handle_search_tmdb();
        break;
    case 'browse_regional':
        handle_browse_regional();
        break;
    default:
        echo json_encode(['error' => 'Invalid API action']);
        http_response_code(400);
        break;
}

function handle_search_tmdb() {
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';
    $type = isset($_GET['type']) ? trim($_GET['type']) : 'multi';
    $apiKey = isset($_GET['apiKey']) ? trim($_GET['apiKey']) : 'ec926176bf467b3f7735e3154238c161';

    if (empty($query)) {
        echo json_encode(['error' => 'Search query is required.']);
        http_response_code(400);
        return;
    }

    $endpoint = "/search/{$type}";
    $params = http_build_query(['query' => $query]);
    $fullEndpoint = "{$endpoint}?{$params}";

    $results = fetchTMDB($fullEndpoint, $apiKey);

    if (isset($results['results'])) {
        echo json_encode($results['results']);
    } else {
        echo json_encode(['error' => 'Failed to fetch search results from TMDB.', 'details' => $results]);
    }
}

function handle_browse_regional() {
    $region = isset($_GET['region']) ? trim($_GET['region']) : 'hollywood';
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $apiKey = isset($_GET['apiKey']) ? trim($_GET['apiKey']) : 'ec926176bf467b3f7735e3154238c161';

    $params = [
        'sort_by' => 'popularity.desc',
        'primary_release_year' => $year,
    ];

    // Region-specific parameters
    switch ($region) {
        case 'anime':
            $params['with_genres'] = 16;
            $params['with_original_language'] = 'ja';
            break;
        case 'kdrama':
            $params['with_genres'] = 18; // Drama
            $params['with_original_language'] = 'ko';
            break;
        case 'hollywood':
        default:
            $params['with_original_language'] = 'en';
            break;
    }

    $endpoint = "/discover/movie?" . http_build_query($params);
    $results = fetchTMDB($endpoint, $apiKey);

    if (isset($results['results'])) {
        echo json_encode($results['results']);
    } else {
        echo json_encode(['error' => 'Failed to fetch browse results from TMDB.', 'details' => $results]);
    }
}
?>
