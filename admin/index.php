<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../config.php';
require_once 'tmdb_handler.php';

$status_message = '';
$status_type = 'info';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'add_movie' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tmdb_id'])) {
        $result = addMovieFromTmdb(trim($_POST['tmdb_id']));
        $status_message = $result;
    } elseif ($action === 'add_series' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tmdb_id'])) {
        $result = addSeriesFromTmdb(trim($_POST['tmdb_id']), trim($_POST['seasons']));
        $status_message = $result;
    }

    if (strpos($status_message, 'Success') === 0) $status_type = 'success';
    elseif (strpos($status_message, 'Info') === 0) $status_type = 'info';
    else $status_type = 'error';
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

        <div id="status-container">
        <?php if ($status_message): ?>
            <div class="status <?php echo $status_type; ?>"><?php echo htmlspecialchars($status_message); ?></div>
        <?php endif; ?>
        </div>


        <nav class="tab-nav">
            <div class="nav-item active" onclick="switchTab(event, 'tmdb-generator')">TMDB Generator</div>
            <div class="nav-item" onclick="switchTab(event, 'manual-input')">Manual Input</div>
            <div class="nav-item" onclick="switchTab(event, 'bulk-operations')">Bulk Operations</div>
            <div class="nav-item" onclick="switchTab(event, 'data-management')">Data Management</div>
        </nav>

        <!-- TMDB Generator Tab -->
        <div id="tmdb-generator" class="tab-content active">
            <div class="card">
                <h2><i class="fas fa-search"></i> TMDB Search & Generate</h2>
                <div class="form-group">
                    <label for="api-key-select">Select TMDB API Key</label>
                    <select id="api-key-select">
                        <option value="ec926176bf467b3f7735e3154238c161">Primary Key</option>
                        <option value="bb51e18edb221e87a05f90c2eb456069">Backup Key 1</option>
                    </select>
                </div>
                <div class="grid grid-2">
                    <div id="search-container">
                        <div class="form-group">
                            <label for="tmdb-search-query">Search Query</label>
                            <input type="text" id="tmdb-search-query" placeholder="e.g., The Matrix">
                        </div>
                        <div class="form-group">
                            <label for="search-type">Content Type</label>
                            <select id="search-type">
                                <option value="multi">All</option>
                                <option value="movie">Movies</option>
                                <option value="tv">TV Shows</option>
                            </select>
                        </div>
                        <button id="search-btn" class="btn btn-primary"><i class="fas fa-search"></i> Search TMDB</button>
                    </div>
                    <div id="browse-container">
                        <div class="form-group">
                            <label for="browse-region">Browse by Region/Genre</label>
                            <select id="browse-region">
                                <option value="hollywood">Hollywood</option>
                                <option value="anime">Anime</option>
                                <option value="kdrama">K-Drama</option>
                            </select>
                        </div>
                         <div class="form-group">
                            <label for="browse-year">Year</label>
                            <input type="number" id="browse-year" placeholder="e.g., 2023" value="<?php echo date("Y"); ?>">
                        </div>
                        <button id="browse-btn" class="btn btn-primary"><i class="fas fa-compass"></i> Browse</button>
                    </div>
                </div>
                 <div id="search-results" class="preview-grid">
                    <!-- Search results will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <!-- Manual Input Tab -->
        <div id="manual-input" class="tab-content">
             <div class="card">
                <h2><i class="fas fa-edit"></i> Manual Content Input</h2>
                <p>This section will contain forms for manually adding Movies, TV Series, and Live TV channels.</p>
            </div>
        </div>
        <!-- Other tabs... -->
        <div id="bulk-operations" class="tab-content">...</div>
        <div id="data-management" class="tab-content">...</div>
    </div>

    <script>
        function switchTab(event, tabName) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchBtn = document.getElementById('search-btn');
            const browseBtn = document.getElementById('browse-btn');
            const resultsContainer = document.getElementById('search-results');
            const statusContainer = document.getElementById('status-container');

            searchBtn.addEventListener('click', function() {
                const query = document.getElementById('tmdb-search-query').value;
                const type = document.getElementById('search-type').value;
                const apiKey = document.getElementById('api-key-select').value;

                if (!query) {
                    alert('Please enter a search query.');
                    return;
                }

                fetchResults(`api_handler.php?action=search_tmdb&query=${encodeURIComponent(query)}&type=${type}&apiKey=${apiKey}`);
            });

            browseBtn.addEventListener('click', function() {
                const region = document.getElementById('browse-region').value;
                const year = document.getElementById('browse-year').value;
                const apiKey = document.getElementById('api-key-select').value;

                fetchResults(`api_handler.php?action=browse_regional&region=${region}&year=${year}&apiKey=${apiKey}`);
            });

            async function fetchResults(url) {
                resultsContainer.innerHTML = '<p>Loading...</p>';
                try {
                    const response = await fetch(url);
                    const results = await response.json();

                    if (results.error) {
                        throw new Error(results.error);
                    }

                    renderResults(results);

                } catch (error) {
                    resultsContainer.innerHTML = `<p style="color: var(--danger);">Error: ${error.message}</p>`;
                }
            }

            function renderResults(results) {
                resultsContainer.innerHTML = '';
                if (results.length === 0) {
                    resultsContainer.innerHTML = '<p>No results found.</p>';
                    return;
                }

                results.forEach(item => {
                    const type = item.media_type || (item.title ? 'movie' : 'tv');
                    if(type === 'person') return; // Skip people in search results

                    const title = item.title || item.name;
                    const year = (item.release_date || item.first_air_date || '').substring(0, 4);
                    const posterPath = item.poster_path ? `https://image.tmdb.org/t/p/w200${item.poster_path}` : 'https://via.placeholder.com/200x300?text=No+Image';

                    const card = document.createElement('div');
                    card.className = 'preview-item';
                    card.innerHTML = `
                        <img src="${posterPath}" alt="${title}">
                        <div class="info">
                            <div class="title">${title}</div>
                            <div class="meta">${year} &bull; ${type.toUpperCase()}</div>
                            <form action="index.php?action=${type === 'movie' ? 'add_movie' : 'add_series'}" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="tmdb_id" value="${item.id}">
                                <button type="submit" class="btn btn-primary btn-small">Generate</button>
                            </form>
                        </div>
                    `;
                    resultsContainer.appendChild(card);
                });
            }
        });
    </script>
</body>
</html>
