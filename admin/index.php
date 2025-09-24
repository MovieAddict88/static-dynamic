<?php
session_start();

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database configuration
require_once '../config.php';

// Placeholder for handling form submissions, will be built out later
$status_message = '';
$status_type = '';

// Include the handler for TMDB operations
require_once 'tmdb_handler.php';

// Handle form submissions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'add_movie' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['tmdb_id'])) {
            $tmdbId = trim($_POST['tmdb_id']);
            $result = addMovieFromTmdb($tmdbId);

            if (strpos($result, 'Success') === 0) {
                $status_type = 'success';
            } elseif (strpos($result, 'Info') === 0) {
                $status_type = 'info';
            } else {
                $status_type = 'error';
            }
            $status_message = $result;
        } else {
            $status_type = 'error';
            $status_message = 'Please provide a TMDB ID.';
        }
    }

    // Future actions (add_series, etc.) will go here
    if ($action === 'add_series' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['tmdb_id'])) {
            $tmdbId = trim($_POST['tmdb_id']);
            $seasons = trim($_POST['seasons']);
            $result = addSeriesFromTmdb($tmdbId, $seasons);

            if (strpos($result, 'Success') === 0) {
                $status_type = 'success';
            } elseif (strpos($result, 'Info') === 0) {
                $status_type = 'info';
            } else {
                $status_type = 'error';
            }
            $status_message = $result;
        } else {
            $status_type = 'error';
            $status_message = 'Please provide a TMDB ID.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CineCraze</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <h1><i class="fas fa-cog"></i> CineCraze Admin</h1>
            <div class="user-info">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
                <a href="change_password.php" title="Change Password"><i class="fas fa-key"></i></a>
                <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <?php if ($status_message): ?>
            <div class="status <?php echo $status_type; ?>"><?php echo htmlspecialchars($status_message); ?></div>
        <?php endif; ?>

        <nav class="tab-nav">
            <div class="nav-item active" onclick="switchTab('tmdb-generator')">TMDB Generator</div>
            <div class="nav-item" onclick="switchTab('manual-input')">Manual Input</div>
            <div class="nav-item" onclick="switchTab('bulk-operations')">Bulk Operations</div>
            <div class="nav-item" onclick="switchTab('data-management')">Data Management</div>
        </nav>

        <!-- TMDB Generator Tab -->
        <div id="tmdb-generator" class="tab-content active">
            <div class="grid grid-2">
                <div class="card">
                    <h2><i class="fas fa-film"></i> Movie from TMDB</h2>
                    <form action="index.php?action=add_movie" method="POST">
                        <div class="form-group">
                            <label for="movie-tmdb-id">TMDB Movie ID</label>
                            <input type="number" id="movie-tmdb-id" name="tmdb_id" placeholder="e.g., 550 (Fight Club)" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Movie</button>
                    </form>
                </div>

                <div class="card">
                    <h2><i class="fas fa-tv"></i> TV Series from TMDB</h2>
                    <form action="index.php?action=add_series" method="POST">
                        <div class="form-group">
                            <label for="series-tmdb-id">TMDB TV Series ID</label>
                            <input type="number" id="series-tmdb-id" name="tmdb_id" placeholder="e.g., 1399 (Game of Thrones)" required>
                        </div>
                        <div class="form-group">
                            <label for="series-seasons">Seasons to Include (optional)</label>
                            <input type="text" id="series-seasons" name="seasons" placeholder="e.g., 1,2,3 or leave empty for all">
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Series</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Manual Input Tab -->
        <div id="manual-input" class="tab-content">
            <div class="card">
                <h2><i class="fas fa-edit"></i> Manual Content Input</h2>
                <p>This section will contain forms for manually adding Movies, TV Series, and Live TV channels.</p>
                <!-- Manual forms will be added here -->
            </div>
        </div>

        <!-- Bulk Operations Tab -->
        <div id="bulk-operations" class="tab-content">
            <div class="card">
                <h2><i class="fas fa-layer-group"></i> Bulk Operations</h2>
                <p>This section will allow bulk importing from TMDB by year or genre, and managing auto-embed server configurations.</p>
                <!-- Bulk operations forms will be added here -->
            </div>
        </div>

        <!-- Data Management Tab -->
        <div id="data-management" class="tab-content">
            <div class="card">
                <h2><i class="fas fa-database"></i> Data Management</h2>
                <p>This section will allow importing/exporting JSON files, viewing content, and managing the database.</p>
                <!-- Data management tools will be added here -->
            </div>
        </div>

    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            // Deactivate all nav items
            document.querySelectorAll('.tab-nav .nav-item').forEach(nav => {
                nav.classList.remove('active');
            });

            // Show the selected tab content
            document.getElementById(tabName).classList.add('active');
            // Activate the selected nav item
            event.currentTarget.classList.add('active');
        }
    </script>

</body>
</html>
