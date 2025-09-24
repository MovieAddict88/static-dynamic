<?php
header('Content-Type: application/json');
require_once 'config.php';

// A simple routing mechanism for API actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_all_content':
        getAllContent();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getAllContent() {
    $pdo = connect_db();
    if (!$pdo) {
        echo json_encode(['error' => 'Database connection failed. Please run the installer.']);
        return;
    }

    try {
        // The final structure we want to build
        $output = [
            'Categories' => []
        ];

        // A map to hold categories while we build them
        $categoriesMap = [];

        // Fetch all content items
        $contentStmt = $pdo->query("SELECT * FROM content ORDER BY type, title");
        while ($content = $contentStmt->fetch()) {
            $mainCategoryName = '';
            if ($content['type'] === 'movie') {
                $mainCategoryName = 'Movies';
            } elseif ($content['type'] === 'series') {
                $mainCategoryName = 'TV Series';
            } elseif ($content['type'] === 'live') {
                $mainCategoryName = 'Live TV';
            }

            if (!isset($categoriesMap[$mainCategoryName])) {
                $categoriesMap[$mainCategoryName] = [
                    'MainCategory' => $mainCategoryName,
                    'SubCategories' => [], // We can populate this later if needed
                    'Entries' => []
                ];
            }

            // Build the entry for the current content item
            $entry = [
                'Title' => $content['title'],
                'Description' => $content['description'],
                'Poster' => $content['poster_url'],
                'Thumbnail' => $content['thumbnail_url'],
                'Rating' => $content['rating'],
                'Duration' => $content['duration'],
                'Year' => $content['release_year'],
                'parentalRating' => $content['parental_rating'],
                'Servers' => [],
                'Seasons' => []
            ];

            if ($content['type'] === 'movie' || $content['type'] === 'live') {
                // Fetch servers for movies and live TV
                $serverStmt = $pdo->prepare("SELECT name, url, drm, license_url as license FROM servers WHERE content_id = ?");
                $serverStmt->execute([$content['id']]);
                $entry['Servers'] = $serverStmt->fetchAll(PDO::FETCH_ASSOC);
                // Cast drm to boolean
                foreach ($entry['Servers'] as &$server) {
                    $server['drm'] = (bool)$server['drm'];
                }
            } elseif ($content['type'] === 'series') {
                // Fetch seasons and episodes for series
                $seasonStmt = $pdo->prepare("SELECT id, season_number, title, poster_url FROM seasons WHERE content_id = ? ORDER BY season_number");
                $seasonStmt->execute([$content['id']]);

                while ($season = $seasonStmt->fetch()) {
                    $seasonEntry = [
                        'Season' => $season['season_number'],
                        'SeasonPoster' => $season['poster_url'],
                        'Episodes' => []
                    ];

                    // Fetch episodes for this season
                    $episodeStmt = $pdo->prepare("SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number");
                    $episodeStmt->execute([$season['id']]);

                    while ($episode = $episodeStmt->fetch()) {
                        $episodeEntry = [
                            'Episode' => $episode['episode_number'],
                            'Title' => $episode['title'],
                            'Duration' => $episode['duration'],
                            'Description' => $episode['description'],
                            'Thumbnail' => $episode['thumbnail_url'],
                            'Servers' => []
                        ];

                        // Fetch servers for this episode
                        $epServerStmt = $pdo->prepare("SELECT name, url, drm, license_url as license FROM servers WHERE episode_id = ?");
                        $epServerStmt->execute([$episode['id']]);
                        $episodeEntry['Servers'] = $epServerStmt->fetchAll(PDO::FETCH_ASSOC);
                        // Cast drm to boolean
                        foreach ($episodeEntry['Servers'] as &$server) {
                            $server['drm'] = (bool)$server['drm'];
                        }

                        $seasonEntry['Episodes'][] = $episodeEntry;
                    }
                    $entry['Seasons'][] = $seasonEntry;
                }
            }

            // Add the fully built entry to the correct category
            $categoriesMap[$mainCategoryName]['Entries'][] = $entry;
        }

        // Convert the map to the final array structure
        $output['Categories'] = array_values($categoriesMap);

        echo json_encode($output, JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred while fetching data: ' . $e->getMessage()]);
    }
}
?>
