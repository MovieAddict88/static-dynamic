<?php
require_once 'check_login.php';
require_once 'config.php';
require_once 'utils.php'; // For get_or_create_genre

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$link) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $poster = $_POST['poster'] ?? '';
    $year = $_POST['year'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $parental_rating = $_POST['parental_rating'] ?? '';
    $country = $_POST['country'] ?? '';

    $stmt = mysqli_prepare($link, "UPDATE content SET title = ?, description = ?, poster = ?, thumbnail = ?, year = ?, rating = ?, parental_rating = ?, country = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssidssi", $title, $description, $poster, $poster, $year, $rating, $parental_rating, $country, $id);

    if (mysqli_stmt_execute($stmt)) {
        // Handle servers for movies and live TV
        if (isset($_POST['servers'])) {
            $servers = json_decode($_POST['servers'], true);
            if (is_array($servers)) {
                // First, clear existing servers for this content
                $clear_stmt = mysqli_prepare($link, "DELETE FROM servers WHERE content_id = ?");
                mysqli_stmt_bind_param($clear_stmt, "i", $id);
                mysqli_stmt_execute($clear_stmt);

                // Then, insert the new ones
                foreach ($servers as $server) {
                    $server_stmt = mysqli_prepare($link, "INSERT INTO servers (content_id, name, url) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($server_stmt, "iss", $id, $server['name'], $server['url']);
                    mysqli_stmt_execute($server_stmt);
                }
            }
        }

        // Handle seasons and episodes for series
        if (isset($_POST['seasons'])) {
            $seasons = json_decode($_POST['seasons'], true);
            if (is_array($seasons)) {
                foreach ($seasons as $season_data) {
                    // For simplicity, we're assuming seasons are not added/deleted in edit mode, only episodes and their servers.
                    // A more complex implementation would handle season creation/deletion.
                    foreach ($season_data['episodes'] as $episode_data) {
                        $episode_id = $episode_data['id'];
                        // Clear existing servers for this episode
                        $clear_episode_stmt = mysqli_prepare($link, "DELETE FROM servers WHERE episode_id = ?");
                        mysqli_stmt_bind_param($clear_episode_stmt, "i", $episode_id);
                        mysqli_stmt_execute($clear_episode_stmt);

                        // Insert new servers for this episode
                        if (isset($episode_data['servers']) && is_array($episode_data['servers'])) {
                            foreach ($episode_data['servers'] as $server) {
                                $server_stmt = mysqli_prepare($link, "INSERT INTO servers (episode_id, name, url) VALUES (?, ?, ?)");
                                mysqli_stmt_bind_param($server_stmt, "iss", $episode_id, $server['name'], $server['url']);
                                mysqli_stmt_execute($server_stmt);
                            }
                        }
                    }
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Content updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update content.']);
    }

    mysqli_close($link);
}
?>