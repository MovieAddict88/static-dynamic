<?php
header('Content-Type: application/json');
require_once '../config.php';

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
        $stmt = $pdo->prepare("
            SELECT g.name FROM genres g
            JOIN content_genres cg ON g.id = cg.genre_id
            WHERE cg.content_id = ?
        ");
        $stmt->execute([$content['id']]);
        $genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $content['SubCategory'] = implode(', ', $genres);

        // Fetch countries
        $stmt = $pdo->prepare("
            SELECT c.name FROM countries c
            JOIN content_countries cc ON c.id = cc.country_id
            WHERE cc.content_id = ?
        ");
        $stmt->execute([$content['id']]);
        $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $content['Country'] = implode(', ', $countries);

        // Fetch servers
        $stmt = $pdo->prepare("SELECT name, url, quality FROM servers WHERE content_id = ? AND season_id IS NULL AND episode_id IS NULL");
        $stmt->execute([$content['id']]);
        $content['Servers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch seasons and episodes for series
        if ($content['type'] === 'series') {
            $stmt = $pdo->prepare("SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number ASC");
            $stmt->execute([$content['id']]);
            $seasons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $content['Seasons'] = [];
            foreach ($seasons as $season) {
                $stmt = $pdo->prepare("SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number ASC");
                $stmt->execute([$season['id']]);
                $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $season['Episodes'] = [];
                foreach ($episodes as $episode) {
                    // Fetch servers for this episode
                    $stmt = $pdo->prepare("SELECT name, url, quality FROM servers WHERE episode_id = ?");
                    $stmt->execute([$episode['id']]);
                    $episode['Servers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    $finalStructure = [
        'Categories' => array_values($categorizedContent)
    ];

    echo json_encode($finalStructure);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>