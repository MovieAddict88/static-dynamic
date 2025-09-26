<?php
function get_public_content() {
    require_once __DIR__ . '/../config.php';
    $pdo = getDBConnection();

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch all content
        $stmt = $pdo->query("SELECT * FROM content ORDER BY id DESC");
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categorizedContent = [];

        foreach ($contents as $content) {
            $categoryName = $content['type'] . 's'; // e.g., 'movies', 'series'

            if (!isset($categorizedContent[$categoryName])) {
                $categorizedContent[$categoryName] = [
                    'MainCategory' => ucfirst($categoryName),
                    'Entries' => []
                ];
            }

            // Fetch genres
            $stmt_genres = $pdo->prepare("
                SELECT g.name FROM genres g
                JOIN content_genres cg ON g.id = cg.genre_id
                WHERE cg.content_id = ?
            ");
            $stmt_genres->execute([$content['id']]);
            $genres = $stmt_genres->fetchAll(PDO::FETCH_COLUMN);
            $content['SubCategory'] = implode(', ', $genres);

            // Fetch countries
            $stmt_countries = $pdo->prepare("
                SELECT c.name FROM countries c
                JOIN content_countries cc ON c.id = cc.country_id
                WHERE cc.content_id = ?
            ");
            $stmt_countries->execute([$content['id']]);
            $countries = $stmt_countries->fetchAll(PDO::FETCH_COLUMN);
            $content['Country'] = implode(', ', $countries);

            // Fetch servers
            $stmt_servers = $pdo->prepare("SELECT name, url, quality FROM servers WHERE content_id = ? AND season_id IS NULL AND episode_id IS NULL");
            $stmt_servers->execute([$content['id']]);
            $content['Servers'] = $stmt_servers->fetchAll(PDO::FETCH_ASSOC);

            // Fetch seasons and episodes for series
            if ($content['type'] === 'series') {
                $stmt_seasons = $pdo->prepare("SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number ASC");
                $stmt_seasons->execute([$content['id']]);
                $seasons = $stmt_seasons->fetchAll(PDO::FETCH_ASSOC);

                $content['Seasons'] = [];
                foreach ($seasons as $season) {
                    $stmt_episodes = $pdo->prepare("SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number ASC");
                    $stmt_episodes->execute([$season['id']]);
                    $episodes = $stmt_episodes->fetchAll(PDO::FETCH_ASSOC);

                    $season['Episodes'] = [];
                    foreach ($episodes as $episode) {
                        // Fetch servers for this episode
                        $stmt_episode_servers = $pdo->prepare("SELECT name, url, quality FROM servers WHERE episode_id = ?");
                        $stmt_episode_servers->execute([$episode['id']]);
                        $episode['Servers'] = $stmt_episode_servers->fetchAll(PDO::FETCH_ASSOC);
                        $season['Episodes'][] = $episode;
                    }
                    $content['Seasons'][] = $season;
                }
            }

            // Map database columns to JSON keys if they differ
            $entry = [
                'Title' => $content['title'],
                'Description' => $content['description'],
                'Year' => $content['year'],
                'Rating' => $content['rating'],
                'Duration' => $content['duration'],
                'Country' => $content['Country'],
                'SubCategory' => $content['SubCategory'],
                'Thumbnail' => $content['thumbnail_url'],
                'Poster' => $content['poster_url'],
                'type' => $content['type'],
                'parentalRating' => $content['parental_rating'],
                'Servers' => $content['Servers']
            ];

            if ($content['type'] === 'series') {
                $entry['Seasons'] = array_map(function($season) {
                    return [
                        'Season' => $season['season_number'],
                        'SeasonPoster' => $season['poster_url'],
                        'Episodes' => array_map(function($episode) {
                            return [
                                'Episode' => $episode['episode_number'],
                                'Title' => $episode['title'],
                                'Description' => $episode['description'],
                                'Thumbnail' => $episode['thumbnail_url'],
                                'Servers' => $episode['Servers']
                            ];
                        }, $season['Episodes'])
                    ];
                }, $content['Seasons']);
            }

            $categorizedContent[$categoryName]['Entries'][] = $entry;
        }

        return ['Categories' => array_values($categorizedContent)];

    } catch (PDOException $e) {
        // In a real application, you might want to log this error instead of returning it.
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

// If the file is called directly via URL, output JSON for debugging or API use
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    header('Content-Type: application/json');
    echo json_encode(get_public_content());
}
?>