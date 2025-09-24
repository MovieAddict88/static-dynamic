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
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    if (($action === 'add_movie' || $action === 'add_series') && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tmdb_id'])) {
        $tmdbId = trim($_POST['tmdb_id']);
        if ($action === 'add_movie') {
            $result = addMovieFromTmdb($tmdbId);
        } else {
            $seasons = isset($_POST['seasons']) ? trim($_POST['seasons']) : '';
            $result = addSeriesFromTmdb($tmdbId, $seasons);
        }

        if (strpos($result, 'Success') === 0) $status_type = 'success';
        elseif (strpos($result, 'Info') === 0) $status_type = 'info';
        else $status_type = 'error';

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => $status_type, 'message' => $result]);
            exit;
        } else {
            $status_message = $result;
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

        <div id="status-container">
        <?php if ($status_message && !$is_ajax): ?>
            <div class="status <?php echo $status_type; ?>"><?php echo htmlspecialchars($status_message); ?></div>
        <?php endif; ?>
        </div>

        <nav class="tab-nav">
            <div class="nav-item active" onclick="switchTab(event, 'tmdb-generator')">TMDB Generator</div>
            <div class="nav-item" onclick="switchTab(event, 'manual-input')">Manual Input</div>
            <div class="nav-item" onclick="switchTab(event, 'bulk-operations')">Bulk Operations</div>
            <div class="nav-item" onclick="switchTab(event, 'data-management')">Data Management</div>
        </nav>

        <div id="tmdb-generator" class="tab-content active">
            <div class="card">
                 <h2><i class="fas fa-key"></i> API Key Management</h2>
                <div class="form-group">
                    <label for="api-key-select">Select TMDB API Key</label>
                    <select id="api-key-select">
                        <option value="ec926176bf467b3f7735e3154238c161">Primary Key (***c161)</option>
                        <option value="bb51e18edb221e87a05f90c2eb456069">Backup Key 1 (***6069)</option>
                        <option value="4a1f2e8c9d3b5a7e6f9c2d1e8b4a5c3f">Backup Key 2 (***a5c3f)</option>
                        <option value="7d9a2b1e4f6c8e5a3b7d9f2e1c4a6b8d">Backup Key 3 (***a6b8d)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-2">
                <div class="card">
                    <h2><i class="fas fa-film"></i> Generate Movie by ID</h2>
                    <form id="generate-movie-form">
                        <div class="form-group">
                            <label for="movie-tmdb-id">TMDB Movie ID</label>
                            <input type="number" id="movie-tmdb-id" name="tmdb_id" placeholder="e.g., 550" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Movie</button>
                    </form>
                </div>
                <div class="card">
                    <h2><i class="fas fa-tv"></i> Generate Series by ID</h2>
                    <form id="generate-series-form">
                        <div class="form-group">
                            <label for="series-tmdb-id">TMDB TV Series ID</label>
                            <input type="number" id="series-tmdb-id" name="tmdb_id" placeholder="e.g., 1399" required>
                        </div>
                        <div class="form-group">
                            <label for="series-seasons">Seasons (optional, comma-separated)</label>
                            <input type="text" id="series-seasons" name="seasons" placeholder="e.g., 1,3 or leave empty for all">
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Series</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h2><i class="fas fa-search"></i> Advanced Search & Browse</h2>
                <div class="grid">
                    <div class="form-group">
                        <label for="search-mode">Mode</label>
                        <select id="search-mode">
                            <option value="search">🔍 Search Mode</option>
                            <option value="hollywood">🎬 Hollywood</option>
                            <option value="anime">🇯🇵 Anime</option>
                            <option value="animation">🎨 Animation</option>
                            <option value="kdrama">🇰🇷 K-Drama</option>
                            <option value="cdrama">🇨🇳 C-Drama</option>
                            <option value="jdrama">🇯🇵 J-Drama</option>
                            <option value="pinoy">🇵🇭 Pinoy Series</option>
                            <option value="thai">🇹🇭 Thai Drama</option>
                            <option value="indian">🇮🇳 Indian Series</option>
                            <option value="turkish">🇹🇷 Turkish Drama</option>
                        </select>
                    </div>

                    <div class="form-group" id="search-query-container">
                        <label for="tmdb-search-query">Search Query</label>
                        <input type="text" id="tmdb-search-query" placeholder="e.g., The Matrix">
                    </div>

                    <div class="form-group" id="content-type-container">
                        <label for="content-type">Content Type</label>
                        <select id="content-type">
                            <option value="multi">All</option>
                            <option value="movie">Movies</option>
                            <option value="tv">TV Shows</option>
                        </select>
                    </div>

                    <div class="form-group" id="year-container" style="display: none;">
                        <label for="browse-year">Year</label>
                        <input type="number" id="browse-year" placeholder="e.g., 2023" value="<?php echo date("Y"); ?>">
                    </div>
                </div>
                <button id="execute-search-btn" class="btn btn-primary"><i class="fas fa-search"></i> Execute</button>
                <div id="search-results" class="preview-grid"></div>
            </div>
        </div>

        <!-- Other Tabs -->
        <div id="manual-input" class="tab-content"><div class="card"><h2>Coming Soon</h2><p>This feature will be implemented in a future update.</p></div></div>
        <div id="bulk-operations" class="tab-content"><div class="card"><h2>Coming Soon</h2><p>This feature will be implemented in a future update.</p></div></div>
        <div id="data-management" class="tab-content"><div class="card"><h2>Coming Soon</h2><p>This feature will be implemented in a future update.</p></div></div>
    </div>

    <script>
        function switchTab(event, tabName) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modeSelect = document.getElementById('search-mode');
            const queryContainer = document.getElementById('search-query-container');
            const typeContainer = document.getElementById('content-type-container');
            const yearContainer = document.getElementById('year-container');
            const executeBtn = document.getElementById('execute-search-btn');
            const resultsContainer = document.getElementById('search-results');
            const statusContainer = document.getElementById('status-container');

            function toggleFilters() {
                const mode = modeSelect.value;
                if (mode === 'search') {
                    queryContainer.style.display = 'block';
                    typeContainer.style.display = 'block';
                    yearContainer.style.display = 'none';
                    typeContainer.querySelector('select').value = 'multi';
                } else {
                    queryContainer.style.display = 'none';
                    typeContainer.style.display = 'block';
                    yearContainer.style.display = 'block';
                }
            }
            modeSelect.addEventListener('change', toggleFilters);
            executeBtn.addEventListener('click', executeSearch);

            async function executeSearch() {
                const apiKey = document.getElementById('api-key-select').value;
                const mode = modeSelect.value;
                let url = '';

                if (mode === 'search') {
                    const query = document.getElementById('tmdb-search-query').value;
                    const type = document.getElementById('content-type').value;
                    if (!query) { alert('Please enter a search query.'); return; }
                    url = `api_handler.php?action=search_tmdb&query=${encodeURIComponent(query)}&type=${type}&apiKey=${apiKey}`;
                } else {
                    const year = document.getElementById('browse-year').value;
                    const type = document.getElementById('content-type').value;
                    url = `api_handler.php?action=browse_regional&region=${mode}&year=${year}&type=${type}&apiKey=${apiKey}`;
                }

                resultsContainer.innerHTML = '<p>Loading...</p>';
                try {
                    const response = await fetch(url);
                    const results = await response.json();
                    if (results.error) throw new Error(results.error);
                    renderResults(results);
                } catch (error) {
                    resultsContainer.innerHTML = `<p style="color: var(--danger);">Error: ${error.message}</p>`;
                }
            }

            function renderResults(results) {
                resultsContainer.innerHTML = '';
                if (!results || results.length === 0) {
                    resultsContainer.innerHTML = '<p>No results found.</p>';
                    return;
                }

                results.forEach(item => {
                    const type = item.media_type || (item.title ? 'movie' : 'tv');
                    if(type === 'person') return;

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
                            <button class="btn btn-primary btn-small generate-btn" data-tmdb-id="${item.id}" data-type="${type === 'tv' ? 'series' : 'movie'}">Generate</button>
                        </div>
                    `;
                    resultsContainer.appendChild(card);
                });
            }

            // Event delegation for generate buttons
            resultsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('generate-btn')) {
                    e.preventDefault();
                    const btn = e.target;
                    const tmdbId = btn.dataset.tmdbId;
                    const type = btn.dataset.type;

                    const form = new FormData();
                    form.append('tmdb_id', tmdbId);

                    btn.textContent = 'Generating...';
                    btn.disabled = true;

                    fetch(`index.php?action=${type === 'movie' ? 'add_movie' : 'add_series'}`, {
                        method: 'POST',
                        body: form,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        showStatus(data.status, data.message);
                        btn.textContent = 'Generate';
                        btn.disabled = false;
                    })
                    .catch(error => {
                        showStatus('error', 'An unexpected error occurred.');
                        btn.textContent = 'Generate';
                        btn.disabled = false;
                    });
                }
            });

            // Handle simple generate forms via AJAX
            document.getElementById('generate-movie-form').addEventListener('submit', handleSimpleGenerate);
            document.getElementById('generate-series-form').addEventListener('submit', handleSimpleGenerate);

            function handleSimpleGenerate(e) {
                e.preventDefault();
                const form = e.target;
                const btn = form.querySelector('button');
                const action = form.id === 'generate-movie-form' ? 'add_movie' : 'add_series';

                btn.textContent = 'Generating...';
                btn.disabled = true;

                fetch(`index.php?action=${action}`, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    showStatus(data.status, data.message);
                    btn.textContent = 'Generate';
                    btn.disabled = false;
                    form.reset();
                })
                .catch(error => {
                    showStatus('error', 'An unexpected error occurred.');
                    btn.textContent = 'Generate';
                    btn.disabled = false;
                });
            }

            function showStatus(type, message) {
                statusContainer.innerHTML = `<div class="status ${type}">${message}</div>`;
                setTimeout(() => {
                    statusContainer.innerHTML = '';
                }, 5000);
            }

            toggleFilters();
        });
    </script>
</body>
</html>
