<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin

require_once '../includes/db.php';

// Check for database connection errors
if ($db->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $db->connect_error]);
    exit();
}

try {
    // Initialize the main structure
    $response = [
        'Categories' => [
            ['MainCategory' => 'Live TV', 'SubCategories' => [], 'Entries' => []],
            ['MainCategory' => 'Movies', 'SubCategories' => [], 'Entries' => []],
            ['MainCategory' => 'TV Series', 'SubCategories' => [], 'Entries' => []]
        ]
    ];

    // Fetch all content
    $sql = "SELECT id, title, description, poster, year, rating, type, video_url, created_at FROM contents ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $entry = [
                'Title' => $row['title'],
                'Description' => $row['description'],
                'Poster' => $row['poster'],
                'Thumbnail' => $row['poster'], // Use poster as thumbnail for simplicity
                'Rating' => (float)$row['rating'],
                'Year' => (int)$row['year'],
                'Servers' => [
                    [
                        'name' => 'Main Server',
                        'url' => $row['video_url']
                    ]
                ]
                // Other fields like SubCategory, Country, Duration etc. can be added if they exist in the DB
            ];

            switch ($row['type']) {
                case 'movie':
                    $response['Categories'][1]['Entries'][] = $entry;
                    break;
                case 'series':
                    // For series, you would typically fetch seasons and episodes here
                    // This is a simplified version
                    $response['Categories'][2]['Entries'][] = $entry;
                    break;
                case 'live':
                    $response['Categories'][0]['Entries'][] = $entry;
                    break;
            }
        }
    }

    $stmt->close();
    $db->close();

    // Output the final JSON
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
}
?>