<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
require_once '../includes/db.php';

// Fetch stats
$movie_count = $db->query("SELECT COUNT(*) FROM contents WHERE type='movie'")->fetch_row()[0];
$series_count = $db->query("SELECT COUNT(*) FROM contents WHERE type='series'")->fetch_row()[0];
$live_count = $db->query("SELECT COUNT(*) FROM contents WHERE type='live'")->fetch_row()[0];
$total_count = $movie_count + $series_count + $live_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CineCraze Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/cinecraze.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🎬 CineCraze Admin Panel</h1>
            <p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?>! Manage your content here.</p>
            <div style="position: absolute; top: 20px; right: 20px;">
                <a href="change_password.php" class="btn btn-secondary">Change Password</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </header>

        <!-- Main Content Area -->
        <div id="data-management" class="tab-content active">
            <div class="grid grid-2">
                <div class="card">
                    <h2>📊 Content Statistics</h2>
                    <div id="data-stats">
                        <div>Movies: <span id="movie-count"><?php echo $movie_count; ?></span></div>
                        <div>Series: <span id="series-count"><?php echo $series_count; ?></span></div>
                        <div>Live TV: <span id="channel-count"><?php echo $live_count; ?></span></div>
                        <div>Total Items: <span id="total-count"><?php echo $total_count; ?></span></div>
                    </div>
                </div>

                <div class="card">
                    <h2>➕ Add New Content</h2>
                    <form action="add_content.php" method="POST">
                        <div class="form-group">
                            <label for="manual-type">Content Type</label>
                            <select id="manual-type" name="type" required>
                                <option value="movie">Movie</option>
                                <option value="series">TV Series</option>
                                <option value="live">Live TV</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="manual-title">Title</label>
                            <input type="text" id="manual-title" name="title" placeholder="Content title" required>
                        </div>
                         <div class="form-group">
                            <label for="manual-description">Description</label>
                            <textarea id="manual-description" name="description" rows="3" placeholder="Content description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="manual-poster">Poster URL</label>
                            <input type="url" id="manual-poster" name="poster" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label for="manual-year">Year</label>
                            <input type="number" id="manual-year" name="year" min="1900" max="2099">
                        </div>
                        <div class="form-group">
                            <label for="manual-rating">Rating</label>
                            <input type="text" id="manual-rating" name="rating" placeholder="e.g., 8.5">
                        </div>
                        <div class="form-group">
                            <label for="video-url">Video URL</label>
                            <input type="url" id="video-url" name="video_url" placeholder="http://example.com/video.mp4" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Content</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h2>📂 Import JSON</h2>
                 <?php if (isset($_SESSION['import_status'])): ?>
                    <div class="status <?php echo $_SESSION['import_status_type']; ?>">
                        <?php echo $_SESSION['import_status']; ?>
                    </div>
                    <?php
                    unset($_SESSION['import_status']);
                    unset($_SESSION['import_status_type']);
                    ?>
                <?php endif; ?>
                <form action="import.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Import `playlist.json` File</label>
                        <input type="file" id="import-file" name="json_file" accept=".json" required>
                        <small style="color: var(--text-secondary); margin-top: 5px; display: block;">
                           Import a JSON file with the specified "Categories" structure. This will add new content and update existing content based on title.
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary">🚀 Import Data</button>
                </form>
            </div>

            <div class="card">
                <h2>👁️ Content Preview & Management</h2>
                <div id="content-preview" class="preview-grid">
                    <?php
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $limit = 20;
                    $offset = ($page - 1) * $limit;

                    $total_results = $db->query("SELECT COUNT(*) FROM contents")->fetch_row()[0];
                    $total_pages = ceil($total_results / $limit);

                    $stmt = $db->prepare("SELECT id, title, poster, type, year, rating FROM contents ORDER BY created_at DESC LIMIT ? OFFSET ?");
                    $stmt->bind_param('ii', $limit, $offset);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="preview-item">';
                            echo '<img src="' . htmlspecialchars($row['poster'] ?: '../assets/images/placeholder.png') . '" alt="' . htmlspecialchars($row['title']) . '" loading="lazy">';
                            echo '<div class="info">';
                            echo '<div class="title">' . htmlspecialchars($row['title']) . '</div>';
                            echo '<div class="meta">' . htmlspecialchars($row['year']) . ' • ' . strtoupper(htmlspecialchars($row['type'])) . ' • ' . htmlspecialchars($row['rating']) . '</div>';
                            echo '<div style="margin-top: 10px;">';
                            // Edit and Delete functionality can be added here with forms pointing to handler scripts
                            // echo '<a href="edit_content.php?id=' . $row['id'] . '" class="btn btn-secondary btn-small">Edit</a>';
                            echo '<form action="delete_content.php" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this item?\');">';
                            echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                            echo '<button type="submit" class="btn btn-danger btn-small">Delete</button>';
                            echo '</form>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No content found.</p>';
                    }
                    $stmt->close();
                    ?>
                </div>
                 <div id="pagination-controls" style="text-align: center; margin: 20px 0;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary btn-small">Previous</a>
                    <?php endif; ?>
                    <span id="page-info" style="margin: 0 15px; font-weight: 600;">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary btn-small">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $db->close(); ?>