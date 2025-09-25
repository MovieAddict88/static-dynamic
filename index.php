<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineCraze</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css@4.1.7/css/flag-icons.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="assets/css/cinecraze.css">
</head>
<body>
    <!-- Notification Bar -->
    <div class="notification-bar" id="notification-bar">
        <p id="notification-message"></p>
        <button class="close-btn" id="close-notification">&times;</button>
    </div>

    <!-- Header -->
    <header>
        <a href="#" class="logo">
            <img src="https://movie-fcs.fwh.is/cinecraze/cinecraze.png" alt="CineCraze Logo">
            <span>CineCraze</span>
        </a>

        <div class="search-container">
            <div class="search-input-container">
                <input type="text" id="search-input" placeholder="Search movies, TV shows..." role="combobox" aria-autocomplete="list" aria-expanded="false" aria-owns="search-results" aria-haspopup="listbox">
                <button class="close-search-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="search-results" id="search-results" role="listbox" aria-label="Search suggestions"></div>
        </div>

        <div class="header-controls">
            <button class="mobile-search-btn" id="mobile-search-btn">
                <i class="fas fa-search"></i>
            </button>
            <button class="theme-toggle" id="refresh-btn" title="Refresh Data">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="theme-toggle" id="theme-toggle">
                <i class="fas fa-moon"></i>
            </button>
            <div class="user-profile">
                <i class="fas fa-user"></i>
            </div>
            <button class="hamburger-btn" id="hamburger-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Carousel -->
        <div class="carousel">
            <div class="carousel-inner" id="carousel-inner">
                <!-- Carousel items will be dynamically added here -->
            </div>
            <div class="carousel-controls">
                <button class="carousel-btn" id="carousel-prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn" id="carousel-next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="carousel-indicators" id="carousel-indicators">
                <!-- Indicators will be dynamically added here -->
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="section-header">
                <h2 class="section-title">Browse Content</h2>
                <div class="view-toggle">
                    <button class="view-btn active" id="view-toggle-btn">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
            </div>

            <div class="mobile-filters-menu">
                <div class="filters-row">
                <div class="filter-group">
                    <label for="genre-filter">Genre</label>
                    <select id="genre-filter" class="filter-select">
                        <option value="all">All Genres</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="year-filter">Year</label>
                    <select id="year-filter" class="filter-select">
                        <option value="all">All Years</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="country-filter">Country</label>
                    <select id="country-filter" class="filter-select">
                        <option value="all">All Countries</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort-filter">Sort By</label>
                    <select id="sort-filter" class="filter-select">
                        <option value="newest">Newest First</option>
                        <option value="popular">Most Popular</option>
                        <option value="rating">Highest Rated</option>
                    </select>
                </div>
            </div>
            </div>
        </div>

        <!-- Content Grid/List -->
        <div class="content-container">
            <div class="content-grid" id="content-grid">
                <!-- Content cards will be dynamically added here -->
            </div>

            <div class="content-list" id="content-list">
                <!-- List view will be dynamically added here -->
            </div>

            <div class="content-grid" id="watch-later-grid" style="display: none;">
                <!-- Watch Later items will be dynamically added here -->
            </div>

            <!-- Loading Spinner -->
            <div class="loading-spinner" id="loading-spinner"></div>

            <!-- Progress Bar -->
            <div class="progress-bar-container" id="progress-bar-container" style="display: none;">
                <div class="progress-bar-text" id="progress-bar-text"></div>
                <div class="progress-bar" id="progress-bar"></div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" id="pagination-container">
                <!-- Pagination buttons will be dynamically added here -->
            </div>
        </div>

        <!-- Viewer Page - YouTube Style -->
        <div class="viewer-page" id="viewer-page">
            <div class="youtube-viewer-container">
                <!-- Back Button -->
                <button class="back-button" id="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span class="back-text">Back to Browse</span>
                    <span class="mobile-back-hint" style="display: none;">← Swipe or tap</span>
                </button>

                <div class="player-container">
                    <video id="player" playsinline controls crossorigin="anonymous">
                        <!-- Source will be set dynamically -->
                    </video>
                    <div id="player-message-area" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; padding: 20px; background-color: var(--youtube-gray); border-radius: var(--radius);"></div>
                </div>

                <div class="viewer-content">
                    <div class="video-details">
                        <div class="video-info">
                            <h1 class="video-title" id="viewer-title">Movie Title</h1>
                            <div class="video-meta">
                                <div class="video-stats">
                                    <span id="viewer-views"><i class="fas fa-eye"></i> 1.2M views</span>
                                    <span id="viewer-date"><i class="fas fa-calendar"></i> Jan 15, 2023</span>
                                </div>
                                <div class="video-actions">
                                    <div class="like-dislike-container">
                                        <div class="icons-box">
                                            <label class="icons">
                                                <span class="btn-label">
                                                    <input class="input-box" type="checkbox" id="like-checkbox" name="like-checkbox">
                                                    <span class="like-text-content" id="like-count-span">24K</span>
                                                    <i id="icon-like-regular" class="fas fa-thumbs-up"></i>
                                                    <i id="icon-like-solid" class="fas fa-thumbs-up"></i>
                                                    <div class="fireworks">
                                                        <div class="checked-like-fx"></div>
                                                    </div>
                                                </span>
                                            </label>
                                            <label class="icons">
                                                <span class="btn-label">
                                                    <input class="input-box" type="checkbox" id="dislike-checkbox" name="dislike-checkbox">
                                                    <i id="icon-dislike-regular" class="fas fa-thumbs-down"></i>
                                                    <i id="icon-dislike-solid" class="fas fa-thumbs-down"></i>
                                                    <span class="dislike-text-content" id="dislike-count-span">1K</span>
                                                    <div class="fireworks">
                                                        <div class="checked-dislike-fx"></div>
                                                    </div>
                                                </span>
                                            </label>
                                            <label class="icons">
                                                <span class="btn-label">
                                                    <input class="input-box" type="checkbox" id="share-checkbox" name="share-checkbox">
                                                    <i id="icon-share-regular" class="fas fa-share"></i>
                                                    <i id="icon-share-solid" class="fas fa-share"></i>
                                                    <span class="share-text-content">Share</span>
                                                    <div class="fireworks">
                                                        <div class="checked-share-fx"></div>
                                                    </div>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="video-description" id="viewer-description">
                            Description will appear here...
                        </div>

                        <div class="server-selector-container" id="server-selector-container">
                            <h3>Select Server</h3>
                            <select class="server-selector" id="server-selector"></select>
                        </div>

                        <div class="episode-selector-container" id="episode-selector-container">
                            <h3>Select Episode</h3>
                            <select class="episode-selector" id="episode-selector">
                                <!-- Episode options will be dynamically added -->
                            </select>
                        </div>

                        <div class="season-selector" id="season-selector">
                            <h3>Seasons</h3>
                            <div class="seasons-grid" id="seasons-grid">
                                <!-- Season cards will be dynamically added -->
                            </div>
                        </div>
                    </div>

                    <div class="related-videos">
                        <h2 class="related-title">Related Content</h2>
                        <div class="related-grid" id="related-grid">
                            <!-- Related content will be dynamically added -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
        <a href="#" class="nav-item active" data-category="all">
            <i class="fas fa-home"></i>
            <span>All</span>
        </a>
        <a href="#" class="nav-item" data-category="movies">
            <i class="fas fa-film"></i>
            <span>Movie</span>
        </a>
        <a href="#" class="nav-item" data-category="series">
            <i class="fas fa-tv"></i>
            <span>Series</span>
        </a>
        <a href="#" class="nav-item" data-category="live">
            <i class="fas fa-broadcast-tower"></i>
            <span>Live</span>
        </a>
        <a href="#" class="nav-item" data-category="watch-later">
            <i class="fas fa-clock"></i>
            <span>Watch Later</span>
        </a>
    </nav>
    <!-- Parental Controls Modals -->
    <div id="parental-controls-modal" class="modal">
        <div class="modal-content parental-controls-content">
            <div class="modal-header">
                <h2 class="modal-title">Parental Controls</h2>
                <button class="close-modal" id="close-parental-controls-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="parental-control-pin-section">
                    <h3>Set a 4-digit PIN to restrict access</h3>
                    <div class="pin-display-container">
                        <div class="pin-display">
                            <span class="pin-dot"></span><span class="pin-dot"></span><span class="pin-dot"></span><span class="pin-dot"></span>
                        </div>
                        <p class="pin-status-text"></p>
                    </div>
                    <div class="pin-pad">
                        <!-- PIN pad buttons will be generated by JS -->
                    </div>
                    <button id="reset-pin-btn" class="parental-btn">Reset PIN</button>
                </div>
                <div class="parental-control-setting-item">
                    <div>
                        <h4>Content Restrictions</h4>
                        <p>Select which content ratings are allowed.</p>
                        <p id="allowed-ratings-display" class="ratings-summary">All ratings allowed</p>
                    </div>
                    <button id="change-ratings-btn" class="parental-btn-secondary">Change</button>
                </div>
                <div class="parental-control-setting-item">
                    <div>
                        <h4>Unrated Content</h4>
                        <p>Allow content without a rating to be played.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="unrated-content-toggle">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div id="ratings-select-modal" class="modal">
        <div class="modal-content ratings-select-content">
            <div class="modal-header">
                <h3 class="modal-title">Select Allowed Ratings</h3>
            </div>
            <div class="modal-body" id="ratings-checkbox-container">
                <!-- Checkboxes will be generated by JS -->
            </div>
            <div class="modal-footer">
                <button class="parental-btn-secondary" id="cancel-ratings-btn">Cancel</button>
                <button class="parental-btn" id="ok-ratings-btn">OK</button>
            </div>
        </div>
    </div>

    <div id="pin-entry-modal" class="modal">
        <div class="modal-content pin-entry-content">
             <div class="modal-header">
                <h3 class="modal-title" id="pin-entry-title">Enter Parental Control PIN</h3>
                <button class="close-modal" id="close-pin-entry-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="pin-display-container">
                    <div class="pin-display-input">
                        <span class="pin-dot"></span><span class="pin-dot"></span><span class="pin-dot"></span><span class="pin-dot"></span>
                    </div>
                    <p class="pin-status-text-input"></p>
                </div>
                <div class="pin-pad-input">
                    <!-- PIN pad buttons will be generated by JS -->
                </div>
                <div class="pin-entry-actions">
                     <button class="parental-btn-secondary" id="cancel-pin-entry-btn">Cancel</button>
                     <button class="parental-btn" id="ok-pin-entry-btn">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Parental Controls Modals -->

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-about">
                <div class="download-section">
                    <a href="https://github.com/MovieAddict88/Movie-Source/raw/main/CineCraze.apk" class="download-button">Download CineCraze App</a>
                    <img src="https://raw.githubusercontent.com/MovieAddict88/Movie-Source/main/cinecraze.png" alt="CineCraze App" class="download-image">
                </div>
                <p>Your ultimate destination for unlimited movies, TV shows, and live television. Stream anytime, anywhere on all your devices.</p>
                <div class="social-links">

<a href="https://www.facebook.com/fagmmmu" target="_blank"><i class="fab fa-facebook-f"></i></a>
<a href="https://www.tiktok.com/@ronaldlimpiadotor?_t=ZS-8xqYEXpLMme&_r=1" target="_blank"><i class="fab fa-tiktok"></i></a>
<a href="https://youtube.com/@ronaldtorrejos?si=z1du-MZnP6OyUmlf" target="_blank"><i class="fab fa-youtube"></i></a>


                </div>
            </div>



            <div class="footer-links-group">
                <h3 class="footer-title">Support</h3>
                <div class="footer-links">
                    <a href="https://movie-fcs.fwh.is/CineMax/contact.html" target="_blank">Contact Us</a>
                    <a href="https://movie-fcs.fwh.is/CineMax/faq.html" target="_blank">FAQ</a>
                    <a href="https://movie-fcs.fwh.is/CineMax/help.html" target="_blank">Help Center</a>
                    <a href="https://movie-fcs.fwh.is/CineMax/terms.html" target="_blank">Terms of Service</a>
                    <a href="https://movie-fcs.fwh.is/CineMax/policy.html" target="_blank">Privacy Policy</a>
                    <a href="#" id="parental-controls-link">Parental Controls</a>
                </div>
            </div>

            <div class="footer-links-group">
                <h3 class="footer-title">This is For Sale Just Contact Me</h3>
                <div class="footer-links">
                    <a href="mailto:ronatorrejos1@gmail.com">
  <i class="fas fa-envelope"></i> ronatorrejos1@gmail.com
</a>
                    <a href="tel:+639663016917">
  <i class="fas fa-phone"></i> +639663016917
</a>
                    <a href="#"><i class="fas fa-map-marker-alt"></i> Cagayan de Oro City 6000, Philippines</a>
                </div>
            </div>
        </div>

        <div class="adsense-banner">
            ADVERTISEMENT
        </div>

        <div class="footer-bottom">
            &copy; 2025 CineCraze. All Rights Reserved.
        </div>
    </footer>

    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script src="https://cdn.dashjs.org/latest/dash.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/4.3.7/shaka-player.compiled.js"></script>
    <script>
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        // --- IndexedDB Caching ---
        const DB_NAME = 'CineCrazeDB';
        const DB_VERSION = 2;
        const STORE_NAME = 'playlistStore';
        const WATCH_LATER_STORE_NAME = 'watchLaterStore';
        const PLAYLIST_KEY = 'mainPlaylist';

        const dbUtil = {
            open: function() {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open(DB_NAME, DB_VERSION);
                    request.onupgradeneeded = event => {
                        const db = event.target.result;
                        if (!db.objectStoreNames.contains(STORE_NAME)) {
                            db.createObjectStore(STORE_NAME, { keyPath: 'id' });
                        }
                        if (!db.objectStoreNames.contains(WATCH_LATER_STORE_NAME)) {
                            db.createObjectStore(WATCH_LATER_STORE_NAME, { keyPath: 'id' });
                        }
                    };
                    request.onsuccess = event => {
                        resolve(event.target.result);
                    };
                    request.onerror = event => {
                        console.error('IndexedDB error:', event.target.errorCode);
                        reject('IndexedDB error: ' + event.target.errorCode);
                    };
                });
            },
            get: function(db, key) {
                return new Promise((resolve, reject) => {
                    if (!db) {
                        reject("Database connection is not available.");
                        return;
                    }
                    const transaction = db.transaction([STORE_NAME], 'readonly');
                    const store = transaction.objectStore(STORE_NAME);
                    const request = store.get(key);
                    request.onsuccess = event => {
                        resolve(event.target.result ? event.target.result.data : null);
                    };
                    request.onerror = event => {
                        console.error('Error getting data from DB:', event.target.errorCode);
                        reject('Error getting data from DB: ' + event.target.errorCode);
                    };
                });
            },
            set: function(db, key, value) {
                return new Promise((resolve, reject) => {
                    if (!db) {
                        reject("Database connection is not available.");
                        return;
                    }
                    const transaction = db.transaction([STORE_NAME], 'readwrite');
                    const store = transaction.objectStore(STORE_NAME);
                    const request = store.put({ id: key, data: value, timestamp: new Date() });
                    request.onsuccess = event => {
                        resolve(event.target.result);
                    };
                    request.onerror = event => {
                        console.error('Error setting data in DB:', event.target.errorCode);
                        reject('Error setting data in DB: ' + event.target.errorCode);
                    };
                });
            },
            clear: function(db) {
                return new Promise((resolve, reject) => {
                    if (!db) {
                        reject("Database connection is not available.");
                        return;
                    }
                    const transaction = db.transaction([STORE_NAME], 'readwrite');
                    const store = transaction.objectStore(STORE_NAME);
                    const request = store.clear();
                    request.onsuccess = () => {
                        console.log("IndexedDB store cleared.");
                        resolve();
                    };
                    request.onerror = event => {
                        console.error('Error clearing DB store:', event.target.errorCode);
                        reject('Error clearing DB store: ' + event.target.errorCode);
                    };
                });
            }
        };

        // Configuration
        const watchLaterDbUtil = {
            open: function() {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open(DB_NAME, DB_VERSION);
                    request.onupgradeneeded = event => {
                        const db = event.target.result;
                        if (!db.objectStoreNames.contains(STORE_NAME)) {
                            db.createObjectStore(STORE_NAME, { keyPath: 'id' });
                        }
                        if (!db.objectStoreNames.contains(WATCH_LATER_STORE_NAME)) {
                            db.createObjectStore(WATCH_LATER_STORE_NAME, { keyPath: 'id' });
                        }
                    };
                    request.onsuccess = event => {
                        resolve(event.target.result);
                    };
                    request.onerror = event => {
                        console.error('IndexedDB error:', event.target.errorCode);
                        reject('IndexedDB error: ' + event.target.errorCode);
                    };
                });
            },
            get: function(db, key) {
                return new Promise((resolve, reject) => {
                    if (!db) {
                        reject("Database connection is not available.");
                        return;
                    }
                    const transaction = db.transaction([WATCH_LATER_STORE_NAME], 'readonly');
                    const store = transaction.objectStore(WATCH_LATER_STORE_NAME);
                    const request = store.get(key);
                    request.onsuccess = event => {
                        resolve(event.target.result);
                    };
                    request.onerror = event => {
                        console.error('Error getting data from DB:', event.target.errorCode);
                        reject('Error getting data from DB: ' + event.target.errorCode);
                    };
                });
            },
            set: function(db, value) {
                return new Promise((resolve, reject) => {
                    if (!db) {
                        return reject("Database connection is not available.");
                    }
                    try {
                        const transaction = db.transaction([WATCH_LATER_STORE_NAME], 'readwrite');
                        transaction.oncomplete = () => {
                            resolve();
                        };
                        transaction.onerror = () => {
                            reject(transaction.error);
                        };
                        const store = transaction.objectStore(WATCH_LATER_STORE_NAME);
                        store.put(value);
                    } catch (error) {
                        reject(error);
                    }
                });
            },
            delete: function(db, key) {
                return new Promise((resolve, reject) => {
                    if (!db) {
                        return reject("Database connection is not available.");
                    }
                    try {
                        const transaction = db.transaction([WATCH_LATER_STORE_NAME], 'readwrite');
                        transaction.oncomplete = () => {
                            resolve();
                        };
                        transaction.onerror = () => {
                            reject(transaction.error);
                        };
                        const store = transaction.objectStore(WATCH_LATER_STORE_NAME);
                        store.delete(key);
                    } catch (error) {
                        reject(error);
                    }
                });
            },
            getAll: function(db) {
                return new Promise((resolve, reject) => {
                    if (!db) {
                        reject("Database connection is not available.");
                        return;
                    }
                    const transaction = db.transaction([WATCH_LATER_STORE_NAME], 'readonly');
                    const store = transaction.objectStore(WATCH_LATER_STORE_NAME);
                    const request = store.getAll();
                    request.onsuccess = event => {
                        resolve(event.target.result);
                    };
                    request.onerror = event => {
                        console.error('Error getting all data from DB:', event.target.errorCode);
                        reject('Error getting all data from DB: ' + event.target.errorCode);
                    };
                });
            }
        };

        const ITEMS_PER_PAGE = 20;
        const LAZY_LOAD_THRESHOLD = 100; // px from bottom to trigger load
        const PLACEHOLDER_IMAGE_URL = 'https://movie-fcs.fwh.is/cinecraze/cinecraze.png';

        let cineData = null;
        let cachedContent = [];
        let currentPage = 1;
        let totalPages = 0;
        let isFetching = false;
        let isInitialLoad = true;

        const elements = {
            header: document.querySelector('header'),
            searchInput: document.getElementById('search-input'),
            searchResults: document.getElementById('search-results'),
            closeSearchBtn: document.querySelector('.close-search-btn'),
            themeToggle: document.getElementById('theme-toggle'),
            viewToggleBtn: document.getElementById('view-toggle-btn'),
            contentGrid: document.getElementById('content-grid'),
            contentList: document.getElementById('content-list'),
            watchLaterGrid: document.getElementById('watch-later-grid'),
            genreFilter: document.getElementById('genre-filter'),
            countryFilter: document.getElementById('country-filter'),
            yearFilter: document.getElementById('year-filter'),
            sortFilter: document.getElementById('sort-filter'),
            viewerPage: document.getElementById('viewer-page'),
            viewerTitle: document.getElementById('viewer-title'),
            viewerDescription: document.getElementById('viewer-description'),
            serverSelectorContainer: document.getElementById('server-selector-container'),
            serverSelector: document.getElementById('server-selector'),
            episodeSelectorContainer: document.getElementById('episode-selector-container'),
            episodeSelector: document.getElementById('episode-selector'),
            seasonSelector: document.getElementById('season-selector'),
            seasonsGrid: document.getElementById('seasons-grid'),
            relatedGrid: document.getElementById('related-grid'),
            player: document.getElementById('player'),
            playerMessageArea: document.getElementById('player-message-area'),
            carouselInner: document.getElementById('carousel-inner'),
            carouselPrev: document.getElementById('carousel-prev'),
            carouselNext: document.getElementById('carousel-next'),
            carouselIndicators: document.getElementById('carousel-indicators'),
            mobileSearchBtn: document.getElementById('mobile-search-btn'),
            searchContainer: document.querySelector('.search-container'),
            backButton: document.getElementById('back-button'),
            loadingSpinner: document.getElementById('loading-spinner'),
            likeCheckbox: document.getElementById('like-checkbox'),
            dislikeCheckbox: document.getElementById('dislike-checkbox'),
            shareCheckbox: document.getElementById('share-checkbox'),
            likeCountSpan: document.getElementById('like-count-span'),
            dislikeCountSpan: document.getElementById('dislike-count-span'),
            progressBarContainer: document.getElementById('progress-bar-container'),
            progressBar: document.getElementById('progress-bar'),
            progressBarText: document.getElementById('progress-bar-text'),
            relatedVideosContainer: document.querySelector('.related-videos'),
            videoDetailsContainer: document.querySelector('.video-details'),
            prevEpisodeBtn: null,
            nextEpisodeBtn: null,
            hamburgerBtn: document.getElementById('hamburger-btn'),
            mobileFiltersMenu: document.querySelector('.mobile-filters-menu'),
            stretchBtn: document.getElementById('stretch-btn'),
            parentalControlsLink: document.getElementById('parental-controls-link'),
            parentalControlsModal: document.getElementById('parental-controls-modal'),
            closeParentalControlsModal: document.getElementById('close-parental-controls-modal'),
            pinDisplay: document.querySelector('.pin-display'),
            pinStatusText: document.querySelector('.pin-status-text'),
            pinPad: document.querySelector('.pin-pad'),
            resetPinBtn: document.getElementById('reset-pin-btn'),
            changeRatingsBtn: document.getElementById('change-ratings-btn'),
            allowedRatingsDisplay: document.getElementById('allowed-ratings-display'),
            unratedContentToggle: document.getElementById('unrated-content-toggle'),
            ratingsSelectModal: document.getElementById('ratings-select-modal'),
            ratingsCheckboxContainer: document.getElementById('ratings-checkbox-container'),
            cancelRatingsBtn: document.getElementById('cancel-ratings-btn'),
            okRatingsBtn: document.getElementById('ok-ratings-btn'),
            pinEntryModal: document.getElementById('pin-entry-modal'),
            closePinEntryModal: document.getElementById('close-pin-entry-modal'),
            pinEntryTitle: document.getElementById('pin-entry-title'),
            pinDisplayInput: document.querySelector('.pin-display-input'),
            pinStatusTextInput: document.querySelector('.pin-status-text-input'),
            pinPadInput: document.querySelector('.pin-pad-input'),
            cancelPinEntryBtn: document.getElementById('cancel-pin-entry-btn'),
            okPinEntryBtn: document.getElementById('ok-pin-entry-btn')
        };

        let currentView = 'grid';
        let currentContentInfo = {};
        let currentContent = [];
        let currentCarouselIndex = 0;
        let playerInstance = null;
        let dashPlayer = null;
        let currentEpisode = null;
        let currentSeason = null;
        let currentSeries = null;
        let isStretched = false;
        let watchLaterItemsSet = new Set();

        let parentalControls = {
            pin: null,
            allowedRatings: [],
            allowUnrated: true
        };
        let currentPinInput = "";
        let pinEntryCallback = null;
        let isSettingPin = false;
        let tempPin = '';

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function sortContent(content, criteria) {
            return [...content].sort((a, b) => {
                switch(criteria) {
                    case 'newest':
                        return (parseInt(b.Year) || 0) - (parseInt(a.Year) || 0);
                    case 'popular':
                        return (parseFloat(b.Rating) || 0) - (parseFloat(a.Rating) || 0);
                    case 'rating':
                        return (parseFloat(b.Rating) || 0) - (parseFloat(a.Rating) || 0);
                    default:
                        return 0;
                }
            });
        }

        function createEpisodeNavigationButtons() {
            elements.prevEpisodeBtn = document.createElement('button');
            elements.prevEpisodeBtn.type = 'button';
            elements.prevEpisodeBtn.className = 'plyr__controls__item plyr__control';
            elements.prevEpisodeBtn.id = 'prev-episode-btn';
            elements.prevEpisodeBtn.setAttribute('aria-label', 'Previous Episode');
            elements.prevEpisodeBtn.innerHTML = '<i class="fas fa-backward"></i>';
            elements.prevEpisodeBtn.style.display = 'none';

            elements.nextEpisodeBtn = document.createElement('button');
            elements.nextEpisodeBtn.type = 'button';
            elements.nextEpisodeBtn.className = 'plyr__controls__item plyr__control';
            elements.nextEpisodeBtn.id = 'next-episode-btn';
            elements.nextEpisodeBtn.setAttribute('aria-label', 'Next Episode');
            elements.nextEpisodeBtn.innerHTML = '<i class="fas fa-forward"></i>';
            elements.nextEpisodeBtn.style.display = 'none';
        }

        function generateStarRating(ratingStr) {
            const rating = parseFloat(ratingStr);
            if (isNaN(rating) || rating < 0 || rating > 10) {
                return '<span>N/A</span>';
            }

            const numStars = 5;
            let starsHtml = '<span class="star-rating">';
            const ratingOutOfFive = rating / 2;

            for (let i = 0; i < numStars; i++) {
                if (ratingOutOfFive >= i + 1) {
                    starsHtml += '<i class="fas fa-star"></i>';
                } else if (ratingOutOfFive >= i + 0.5) {
                    starsHtml += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    starsHtml += '<i class="far fa-star"></i>';
                }
            }
            starsHtml += '</span>';
            return starsHtml;
        }

        const countryNameToCodeMap = {
            "USA": "us", "United States": "us", "United States of America": "us",
            "UK": "gb", "United Kingdom": "gb",
            "Canada": "ca",
            "Japan": "jp",
            "South Korea": "kr", "Korea": "kr",
            "France": "fr",
            "Germany": "de",
            "India": "in",
            "China": "cn",
            "Spain": "es",
            "Italy": "it",
            "Australia": "au",
            "Brazil": "br",
            "Mexico": "mx",
            "Philippines": "ph",
        };

        function getCountryFlagHtml(countryName) {
            if (!countryName) return '';
            const countryCode = countryNameToCodeMap[countryName.trim()] || countryName.toLowerCase().replace(/\s+/g, '-');
            if (countryCode) {
                return `<span class="fi fi-${countryCode.toLowerCase()}"></span>`;
            }
            return '';
        }

        async function init() {
            try {
                const db = await watchLaterDbUtil.open();
                const watchLaterItems = await watchLaterDbUtil.getAll(db);
                db.close();
                watchLaterItemsSet = new Set(watchLaterItems.map(item => item.id));
            } catch (error) {
                console.error("Failed to cache watch later items:", error);
            }

            setupParentalControls();
            createEpisodeNavigationButtons();
            fetchData().then(() => {
                renderCarousel();
                renderContentFilters();
                renderContent();
                setupEventListeners();
                setupMobileBackButton();
                updateCarousel();
                setupLazyLoading();
                history.replaceState({ page: 'browse' }, 'Browse Content', window.location.pathname + window.location.search);
            });
        }

        async function fetchData() {
            let db;
            try {
                db = await dbUtil.open();
                const cachedData = await dbUtil.get(db, PLAYLIST_KEY);

                if (cachedData) {
                    cineData = cachedData;
                    console.log("✅ Loaded data from IndexedDB cache");
                    return;
                }

                console.log("ℹ️ No cache found. Fetching from API...");
                elements.progressBarContainer.style.display = 'block';
                elements.loadingSpinner.style.display = 'none';
                elements.progressBarText.textContent = `Fetching data from API...`;

                const response = await fetch('api/index.php');
                if (!response.ok) {
                    throw new Error(`API request failed with status ${response.status}`);
                }

                cineData = await response.json();

                if (!cineData || !cineData.Categories) {
                    throw new Error("Invalid data format from API");
                }

                await dbUtil.set(db, PLAYLIST_KEY, cineData);
                console.log("✅ Loaded and cached data from API");

            } catch (err) {
                console.error("❌ Data loading failed:", err);
                const errorMessage = document.createElement('div');
                errorMessage.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: var(--youtube-gray);
                    padding: 20px;
                    border-radius: 8px;
                    text-align: center;
                    z-index: 10000;
                    max-width: 400px;
                `;
                errorMessage.innerHTML = `
                    <h3>⚠️ Data Loading Failed</h3>
                    <p>Unable to load content from the API. Please ensure the backend is running correctly.</p>
                    <button onclick="this.parentElement.remove(); window.location.reload();" style="
                        background: var(--primary);
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 4px;
                        cursor: pointer;
                        margin-top: 10px;
                    ">Retry</button>
                `;
                document.body.appendChild(errorMessage);
            } finally {
                if (db) db.close();
                elements.progressBarContainer.style.display = 'none';
                elements.loadingSpinner.style.display = 'none';
            }
        }

        function getRandomItems(arr, count) {
            if (!arr || arr.length === 0) return [];
            const shuffled = [...arr].sort(() => 0.5 - Math.random());
            return shuffled.slice(0, Math.min(count, arr.length));
        }

        function renderCarousel() {
            if (!cineData || !cineData.Categories || cineData.Categories.length < 1) {
                console.warn("Carousel data is not as expected. Skipping render.");
                return;
            }

            const featuredContent = [];
            const moviesCategory = cineData.Categories.find(cat => cat.MainCategory.toLowerCase().includes('movie'));
            const seriesCategory = cineData.Categories.find(cat => cat.MainCategory.toLowerCase().includes('series'));
            const liveCategory = cineData.Categories.find(cat => cat.MainCategory.toLowerCase().includes('live'));

            if (moviesCategory && moviesCategory.Entries) {
                getRandomItems(moviesCategory.Entries, 3).forEach(movie => {
                    featuredContent.push({
                        type: 'movie',
                        title: movie.Title,
                        description: movie.Description,
                        image: movie.Poster,
                        originalItem: movie
                    });
                });
            }

            if (seriesCategory && seriesCategory.Entries) {
                getRandomItems(seriesCategory.Entries, 1).forEach(series => {
                    featuredContent.push({
                        type: 'series',
                        title: series.Title,
                        description: series.Description || `Popular ${series.SubCategory} series`,
                        image: series.Poster,
                        originalItem: series
                    });
                });
            }

            if (liveCategory && liveCategory.Entries) {
                getRandomItems(liveCategory.Entries, 1).forEach(live => {
                    featuredContent.push({
                        type: 'live',
                        title: live.Title,
                        description: live.Description,
                        image: live.Poster,
                        originalItem: live
                    });
                });
            }

            shuffleArray(featuredContent);

            elements.carouselInner.innerHTML = '';
            elements.carouselIndicators.innerHTML = '';

            featuredContent.forEach((item, index) => {
                const carouselItem = document.createElement('div');
                carouselItem.className = 'carousel-item';
                const year = item.originalItem && item.originalItem.Year ? item.originalItem.Year : '';
                const ratingHtml = item.originalItem ? generateStarRating(item.originalItem.Rating) : '';

                carouselItem.innerHTML = `
                    <img src="${item.image}" alt="${item.title}" onerror="this.onerror=null; this.src='${PLACEHOLDER_IMAGE_URL}';">
                    <div class="carousel-content">
                        <h2>${item.title}</h2>
                        <div class="carousel-meta">
                            ${ratingHtml}
                            ${year ? `<span class="carousel-year">${year}</span>` : ''}
                        </div>
                        <p>${item.description}</p>
                    </div>
                `;
                elements.carouselInner.appendChild(carouselItem);

                const indicator = document.createElement('div');
                indicator.className = 'indicator';
                indicator.dataset.index = index;
                if (index === 0) indicator.classList.add('active');
                elements.carouselIndicators.appendChild(indicator);

                indicator.addEventListener('click', () => {
                    currentCarouselIndex = index;
                    updateCarousel();
                });

                carouselItem.addEventListener('click', () => {
                    if (item.originalItem) {
                        openViewer({ ...item.originalItem, type: item.type });
                    } else {
                        console.error("Carousel item is missing originalItem data:", item);
                    }
                });
            });
        }

        function renderContentFilters() {
            if (!cineData || !cineData.Categories) return;

            const genres = new Set();
            const countries = new Set();

            cineData.Categories.forEach(category => {
                category.Entries.forEach(entry => {
                    if (entry.SubCategory) genres.add(entry.SubCategory);
                    if (entry.Country) countries.add(entry.Country);
                });
            });

            const genreFilter = elements.genreFilter;
            genres.forEach(genre => {
                const option = document.createElement('option');
                option.value = genre.toLowerCase();
                option.textContent = genre;
                genreFilter.appendChild(option);
            });

            const countryFilter = elements.countryFilter;
            countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.toLowerCase();
                option.textContent = country;
                countryFilter.appendChild(option);
            });

            const yearFilter = elements.yearFilter;
            const years = new Set();
            cineData.Categories.forEach(category => {
                category.Entries.forEach(entry => {
                    if (entry.Year) {
                        years.add(entry.Year);
                    }
                });
            });

            const sortedYears = Array.from(years).sort((a, b) => b - a);
            sortedYears.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearFilter.appendChild(option);
            });
        }

        async function renderContent(category = 'all') {
            if (!cineData || !cineData.Categories) return;

            const filtersSection = document.querySelector('.filters-section');

            currentPage = 1;
            currentContent = [];

            if (category === 'watch-later') {
                filtersSection.style.display = 'none';
                const db = await watchLaterDbUtil.open();
                const watchLaterItems = await watchLaterDbUtil.getAll(db);
                db.close();
                currentContent = watchLaterItems.map(item => ({ ...item, type: item.type || 'movie' }));

                totalPages = Math.ceil(currentContent.length / ITEMS_PER_PAGE);
                cachedContent = [...currentContent];
                currentView = 'watch-later';
                renderCurrentView();
                renderPaginationControls();
                setupLazyLoading();
                return;
            } else {
                const icon = elements.viewToggleBtn.querySelector('i');
                if (icon.classList.contains('fa-list')) {
                    currentView = 'list';
                } else {
                    currentView = 'grid';
                }
                filtersSection.style.display = 'block';
            }

            const genre = elements.genreFilter.value.toLowerCase();
            const country = elements.countryFilter.value.toLowerCase();
            const year = elements.yearFilter.value;
            const sortBy = elements.sortFilter.value;

            cineData.Categories.forEach(cat => {
                if (category === 'all' || cat.MainCategory.toLowerCase().includes(category)) {
                    cat.Entries.forEach(entry => {
                        const genreMatch = genre === 'all' ||
                            (entry.SubCategory && entry.SubCategory.toLowerCase().includes(genre));
                        const countryMatch = country === 'all' ||
                            (entry.Country && entry.Country.toLowerCase().includes(country));
                        const yearMatch = year === 'all' || (entry.Year && entry.Year.toString() === year);

                        if (genreMatch && countryMatch && yearMatch && isContentAllowed(entry)) {
                            currentContent.push({
                                ...entry,
                                type: cat.MainCategory.toLowerCase().includes('movie') ? 'movie' :
                                    cat.MainCategory.toLowerCase().includes('series') ? 'series' : 'live'
                            });
                        }
                    });
                }
            });

            if (isInitialLoad) {
                currentContent = shuffleArray(currentContent);
                isInitialLoad = false;
            }
            else {
                currentContent = sortContent(currentContent, sortBy);
            }

            totalPages = Math.ceil(currentContent.length / ITEMS_PER_PAGE);
            cachedContent = [...currentContent];
            renderCurrentView();
            renderPaginationControls();
            setupLazyLoading();
        }

        function renderCurrentView() {
            elements.contentGrid.style.display = 'none';
            elements.contentList.style.display = 'none';
            elements.watchLaterGrid.style.display = 'none';

            let container;
            let viewClass = 'grid';

            if (currentView === 'watch-later') {
                container = elements.watchLaterGrid;
                container.style.display = 'grid';
            } else if (currentView === 'grid') {
                container = elements.contentGrid;
                container.style.display = 'grid';
            } else {
                container = elements.contentList;
                container.style.display = 'flex';
                viewClass = 'list';
            }

            container.innerHTML = '';

            const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;
            const itemsToRender = currentContent.slice(startIndex, endIndex);

            itemsToRender.forEach(item => {
                const card = createContentCard(item, viewClass);
                container.appendChild(card);
            });

            setupLazyLoading();
        }

        function createContentCard(item, viewType) {
            const card = document.createElement('div');
            card.className = `content-card ${viewType}`;
            card.dataset.id = item.Title.replace(/\s+/g, '-').toLowerCase();
            card.dataset.type = item.type;

            let badge = '';
            if (item.type === 'movie') {
                badge = '<div class="card-badge badge-movie">MOVIE</div>';
            } else if (item.type === 'series') {
                badge = '<div class="card-badge badge-series">SERIES</div>';
            } else if (item.type === 'live') {
                badge = '<div class="card-badge badge-live">LIVE</div>';
            }

            let deleteBtn = '';
            let watchLaterIcon = '';
            if (currentView === 'watch-later') {
                deleteBtn = `<button class="delete-watch-later-btn" data-id="${item.id}"><i class="fas fa-trash"></i></button>`;
            } else {
                const isActive = watchLaterItemsSet.has(item.Title);
                watchLaterIcon = `<button class="card-watch-later-btn ${isActive ? 'active' : ''}" data-id="${item.Title.replace(/\s+/g, '-').toLowerCase()}"><i class="fas fa-clock"></i></button>`;
            }

            card.innerHTML = `
                <div class="card-img">
                    <img data-src="${item.Thumbnail || item.Poster}" alt="${item.Title}" class="lazy-image" loading="lazy" onerror="this.onerror=null; this.src='${PLACEHOLDER_IMAGE_URL}';">
                    ${badge}
                    ${deleteBtn}
                    ${viewType === 'grid' ? watchLaterIcon : ''}
                </div>
                <div class="card-info">
                    <div class="card-title-container">
                        <h3 class="card-title">${item.Title}</h3>
                        ${viewType === 'list' ? watchLaterIcon : ''}
                    </div>
                    <div class="card-meta">
                        ${generateStarRating(item.Rating)}
                        ${viewType === 'list' ?
                            `<span class="meta-netflix-style">
                                ${item.Year ? `<span>${item.Year}</span>` : ''}
                                ${item.Country ? `<span class="meta-country">${getCountryFlagHtml(item.Country)} ${item.Country}</span>` : ''}
                                ${item.Duration ? `<span>${item.Duration}</span>` : ''}
                            </span>` :
                            `${item.Duration ? `<span><i class="fas fa-clock"></i> ${item.Duration}</span>` : ''}
                             ${item.Country ? `<span class="meta-country">${getCountryFlagHtml(item.Country)} ${item.Country}</span>` : ''}`
                        }
                    </div>
                    ${viewType === 'list' ? `<p class="card-description">${item.Description || ''}</p>` : ''}
                </div>
            `;

            if (currentView === 'watch-later') {
                const btn = card.querySelector('.delete-watch-later-btn');
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    deleteFromWatchLater(item.id);
                });
            } else {
                const watchLaterBtn = card.querySelector('.card-watch-later-btn');
                if (watchLaterBtn) {
                    watchLaterBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        toggleWatchLater(item, e.currentTarget);
                    });
                }
            }

            card.addEventListener('click', () => openViewer(item));
            return card;
        }

        function renderPaginationControls() {
            const paginationContainer = document.getElementById('pagination-container');
            paginationContainer.innerHTML = '';
            if (totalPages <= 1) return;

            const createPageButton = (page, text = page) => {
                const button = document.createElement('button');
                button.classList.add('pagination-btn');
                button.textContent = text;
                if (page === currentPage) {
                    button.classList.add('active');
                }
                button.addEventListener('click', () => {
                    currentPage = page;
                    renderCurrentView();
                    renderPaginationControls();
                    if (window.innerWidth <= 576) {
                        document.getElementById('content-grid').scrollIntoView({ behavior: 'smooth' });
                    }
                });
                return button;
            };

            const createEllipsis = () => {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.classList.add('pagination-ellipsis');
                return ellipsis;
            };

            const prevButton = document.createElement('button');
            prevButton.classList.add('pagination-btn');
            prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderCurrentView();
                    renderPaginationControls();
                    if (window.innerWidth <= 576) {
                        document.getElementById('content-grid').scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
            paginationContainer.appendChild(prevButton);

            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) {
                    paginationContainer.appendChild(createPageButton(i));
                }
            } else {
                if (currentPage < 5) {
                    for (let i = 1; i <= 5; i++) {
                        paginationContainer.appendChild(createPageButton(i));
                    }
                    paginationContainer.appendChild(createEllipsis());
                    paginationContainer.appendChild(createPageButton(totalPages));
                } else if (currentPage > totalPages - 4) {
                    paginationContainer.appendChild(createPageButton(1));
                    paginationContainer.appendChild(createEllipsis());
                    for (let i = totalPages - 4; i <= totalPages; i++) {
                        paginationContainer.appendChild(createPageButton(i));
                    }
                } else {
                    paginationContainer.appendChild(createPageButton(1));
                    paginationContainer.appendChild(createEllipsis());
                    for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                        paginationContainer.appendChild(createPageButton(i));
                    }
                    paginationContainer.appendChild(createEllipsis());
                    paginationContainer.appendChild(createPageButton(totalPages));
                }
            }

            const nextButton = document.createElement('button');
            nextButton.classList.add('pagination-btn');
            nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderCurrentView();
                    renderPaginationControls();
                    if (window.innerWidth <= 576) {
                        document.getElementById('content-grid').scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
            paginationContainer.appendChild(nextButton);
        }

        function setupLazyLoading() {
            const lazyImages = document.querySelectorAll('.lazy-image');

            const lazyLoad = () => {
                lazyImages.forEach(img => {
                    if (img.classList.contains('loaded')) return;

                    const rect = img.getBoundingClientRect();
                    if (rect.top < window.innerHeight + LAZY_LOAD_THRESHOLD) {
                        img.src = img.dataset.src;
                        img.onload = () => {
                            img.classList.add('loaded');
                        };
                    }
                });
            };

            lazyLoad();
            window.addEventListener('scroll', lazyLoad);
            window.addEventListener('resize', lazyLoad);
        }

        function openViewer(content) {
            if (!isContentAllowed(content)) {
                elements.pinEntryTitle.textContent = "Enter PIN to Watch";
                showModal(elements.pinEntryModal);
                pinEntryCallback = () => {
                    openViewerInternal(content);
                };
                return;
            }
            openViewerInternal(content);
        }

        function openViewerInternal(content) {
            if (playerInstance) {
                playerInstance.destroy();
                playerInstance = null;
            }
            if (dashPlayer) {
                dashPlayer.reset();
                dashPlayer = null;
            }

            const playerContainer = document.querySelector('.player-container');
            playerContainer.innerHTML = '<video id="player" playsinline controls></video>';
            elements.player = document.getElementById('player');

            currentContentInfo = content;

            incrementViewCount(content.Title);
            history.pushState({ page: 'viewer', contentId: content.Title }, 'View Content', '#viewer');

            document.querySelector('.carousel').style.display = 'none';
            document.querySelector('.filters-section').style.display = 'none';
            document.querySelector('.content-container').style.display = 'none';

            const setupViewerAndPlay = () => {
                elements.viewerTitle.textContent = content.Title;
                elements.viewerDescription.textContent = content.Description || 'No description available.';
                if (elements.serverSelectorContainer) {
                    elements.serverSelectorContainer.style.display = 'none';
                }
                if (elements.serverSelector) {
                    elements.serverSelector.innerHTML = '';
                }
                elements.episodeSelectorContainer.style.display = 'none';
                elements.episodeSelector.innerHTML = '<option value="">Select Episode</option>';
                elements.seasonSelector.style.display = 'none';
                if (elements.episodeSelectorContainer.parentNode !== elements.videoDetailsContainer) {
                    elements.videoDetailsContainer.appendChild(elements.episodeSelectorContainer);
                }
                if (elements.seasonSelector.parentNode !== elements.videoDetailsContainer) {
                    elements.videoDetailsContainer.appendChild(elements.seasonSelector);
                }

                currentSeason = null;
                currentEpisode = null;
                currentSeries = content;

                if (content.type !== 'series' && content.Servers) {
                    updatePlayerSource(content.Servers);
                }
                elements.seasonsGrid.innerHTML = '';

                if (content.type === 'series' && content.Seasons) {
                    if (elements.relatedVideosContainer) {
                        elements.relatedVideosContainer.prepend(elements.seasonSelector);
                        elements.relatedVideosContainer.prepend(elements.episodeSelectorContainer);
                    }
                    elements.seasonSelector.style.display = 'block';
                    content.Seasons.forEach(season => {
                        const seasonCard = document.createElement('div');
                        seasonCard.className = 'season-card';
                        seasonCard.innerHTML = `
                            <img src="${season.SeasonPoster || content.Thumbnail}" alt="Season ${season.Season}" onerror="this.onerror=null; this.src='${PLACEHOLDER_IMAGE_URL}';">
                            <div class="season-info">
                                <h4>Season ${season.Season}</h4>
                            </div>
                        `;
                        seasonCard.addEventListener('click', () => {
                            openSeason(season);
                        });
                        elements.seasonsGrid.appendChild(seasonCard);
                    });
                    if (content.Seasons.length > 0) {
                        openSeason(content.Seasons[0]);
                    } else {
                        updateNavigationButtonsState();
                    }
                } else {
                    updateNavigationButtonsState();
                }

                elements.relatedGrid.innerHTML = '';
                const relatedContent = cachedContent.filter(item => item.Title !== content.Title)
                                               .sort(() => 0.5 - Math.random()).slice(0, 8);
                relatedContent.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'related-card';
                    const ratingHtml = generateStarRating(item.Rating);
                    let metaNetflixStyle = '<span class="meta-netflix-style">';
                    if (item.Year) metaNetflixStyle += `<span>${item.Year}</span>`;
                    if (item.Country) metaNetflixStyle += `<span class="meta-country">${getCountryFlagHtml(item.Country)} ${item.Country}</span>`;
                    if (item.Duration) metaNetflixStyle += `<span>${item.Duration}</span>`;
                    metaNetflixStyle += '</span>';

                    card.innerHTML = `
                        <div class="related-thumbnail">
                            <img src="${item.Thumbnail || item.Poster}" alt="${item.Title}" onerror="this.onerror=null; this.src='${PLACEHOLDER_IMAGE_URL}';">
                        </div>
                        <div class="related-info">
                            <h4>${item.Title}</h4>
                            <div class="related-meta-stars">${ratingHtml}</div>
                            <div class="related-meta-details">${metaNetflixStyle}</div>
                            <div class="related-channel">CineCraze</div>
                        </div>
                    `;
                    card.addEventListener('click', () => openViewer(item));
                    elements.relatedGrid.appendChild(card);
                });
            };

            if (!playerInstance) {
                playerInstance = new Plyr('#player', {
                    controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'fullscreen'],
                    youtube: { noCookie: true, rel: 0, showinfo: 0, iv_load_policy: 3 },
                    quality: {
                        default: 720,
                        options: [1080, 720, 480, 360, 240],
                    },
                    autoplay: false,
                    muted: false,
                });

                playerInstance.once('ready', event => {
                    setupViewerAndPlay();
                    addStretchButtonToPlayer();
                    playerInstance.on('enterfullscreen', () => {
                        if (screen.orientation && screen.orientation.lock) {
                            screen.orientation.lock('landscape').catch(error => {
                                console.log('Orientation lock failed: ', error);
                            });
                        }
                    });
                    playerInstance.on('exitfullscreen', () => {
                        if (screen.orientation && screen.orientation.unlock) {
                            screen.orientation.unlock();
                        }
                        if (elements.searchContainer) {
                            elements.searchContainer.classList.remove('show');
                        }
                    });
                });

                document.addEventListener('fullscreenchange', () => {
                    if (document.fullscreenElement && document.fullscreenElement.classList.contains('external-content-iframe')) {
                        if (screen.orientation && screen.orientation.lock) {
                            screen.orientation.lock('landscape').catch(error => {
                                console.log('Orientation lock failed: ', error);
                            });
                        }
                    }
                });
            } else {
                setupViewerAndPlay();
            }

            elements.viewerPage.style.display = 'block';
            window.scrollTo(0, 0);

            updateLikeDislikeUI(content.Title);

            const setupListener = (element, handler) => {
                if (!element) return;
                if (element._changeHandler) {
                    element.removeEventListener('change', element._changeHandler);
                }
                element._changeHandler = handler;
                element.addEventListener('change', element._changeHandler);
            };

            setupListener(elements.likeCheckbox, handleLike);
            setupListener(elements.dislikeCheckbox, handleDislike);
            setupListener(elements.shareCheckbox, () => {
                handleShareVideo();
                setTimeout(() => {
                    if (elements.shareCheckbox) {
                        elements.shareCheckbox.checked = false;
                    }
                }, 500);
            });

            const watchLaterBtn = document.getElementById('watch-later-btn');
            if (watchLaterBtn) {
                const newToggleHandler = () => toggleWatchLater(content);
                if (watchLaterBtn._toggleHandler) {
                    watchLaterBtn.removeEventListener('click', watchLaterBtn._toggleHandler);
                }
                watchLaterBtn.addEventListener('click', newToggleHandler);
                watchLaterBtn._toggleHandler = newToggleHandler;
                updateWatchLaterButton(content.Title);
            }
        }

        function getAutoplayUrl(url) {
            if (!url) return '';
            const separator = url.includes('?') ? '&' : '?';
            if (url.includes('vidfast.pro')) {
                return url + separator + 'autoPlay=true';
            }
            return url + separator + 'autoplay=true';
        }

        async function playUrl(url, servers = []) {
            if (!url || !playerInstance) return;

            const playerContainer = document.querySelector('.player-container');
            let iframe = playerContainer.querySelector('iframe.external-content-iframe');

            if (dashPlayer) {
                dashPlayer.reset();
                dashPlayer = null;
            }

            if (playerInstance && typeof playerInstance.stop === 'function') {
                playerInstance.stop();
            }
            elements.playerMessageArea.style.display = 'none';
            elements.playerMessageArea.textContent = '';

            const isEmbed = isGoogleDriveUrl(url) || isMegaNzUrl(url) || isVidsrcUrl(url) || isVidjoyUrl(url) || isVidsrcMeUrl(url) || isVidsrcToUrl(url) || isVidsrcXyzUrl(url) || isGoDrivePlayerUrl(url) || isVidLinkProUrl(url) || is2EmbedUrl(url) || isEmbedSuUrl(url) || isAutoEmbedUrl(url) || isVideasyUrl(url) || isVidfastEmbedUrl(url) || isVidplusUrl(url);

            if (isEmbed) {
                elements.player.style.display = 'none';

                if (iframe) {
                    iframe.src = 'about:blank';
                    iframe.remove();
                }

                iframe = document.createElement('iframe');
                iframe.className = 'external-content-iframe';
                iframe.style.position = 'absolute';
                iframe.style.top = '0';
                iframe.style.left = '0';
                iframe.style.width = '100%';
                iframe.style.height = '100%';
                iframe.style.border = 'none';
                iframe.setAttribute('allowfullscreen', '');
                iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
                iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation allow-forms');
                playerContainer.appendChild(iframe);

                let embedUrl = url;
                if (isGoogleDriveUrl(url)) {
                    const fileId = extractGoogleDriveId(url);
                    if (fileId) embedUrl = `https://drive.google.com/file/d/${fileId}/preview`;
                } else if (isMegaNzUrl(url)) {
                    embedUrl = url.replace('/file/', '/embed/');
                } else if (isVidfastEmbedUrl(url)) {
                    embedUrl = url.replace('/movie/', '/embed/movie/').replace('/tv/', '/embed/tv/');
                } else if (isVidplusUrl(url)) {
                    embedUrl = url.replace('https://vidplus.to', 'https://player.vidplus.to').replace('/movie/', '/embed/movie/').replace('/tv/', '/embed/tv/');
                }

                iframe.src = getAutoplayUrl(embedUrl);

                if (servers && servers.length > 1) {
                    setTimeout(() => updateServerDropdown(servers, { url: url }), 100);
                }

                return;
            } else {
                if (iframe) {
                    iframe.style.display = 'none';
                    iframe.src = 'about:blank';
                    iframe.remove();
                }

                elements.player.style.display = 'block';
                hideEmbeddedServerSelector();
            }

            if (isYouTubeUrl(url)) {
                const videoId = extractYouTubeId(url);
                if (videoId) {
                    playerInstance.source = {
                        type: 'video',
                        sources: [{
                            src: videoId,
                            provider: 'youtube'
                        }]
                    };
                    return;
                }
            }
            if (url.includes('.mpd')) {
                const formatInfo = detectChannelFormat(url);
                console.log(`📺 Playing ${formatInfo.type} channel (${formatInfo.quality})`);

                updateLoadingStatus(`Loading ${formatInfo.type} channel (${formatInfo.quality})...`);

                const hasDRMRequirements = await detectChannelDRMRequirements(url, servers);

                if (hasDRMRequirements) {
                    console.log('🔒 Channel has DRM requirements - using Shaka Player with DRM');
                    updateLoadingStatus('Configuring DRM and loading stream...');
                    await handleMPDStream(url, servers);
                } else {
                    console.log('📺 Channel has no DRM requirements - using optimized non-DRM MPD player');
                    updateLoadingStatus('Loading stream without DRM...');
                    await playNonDRMMPD(url);
                    showPlayingStatus('Non-DRM MPD Stream', 'Playing Successfully');
                }
                return;
            }

            const formatInfo = detectChannelFormat(url);
            console.log(`📺 Playing ${formatInfo.type} content (${formatInfo.quality}) with ${formatInfo.recommendedPlayer}`);

            let mimeType = 'video/mp4';
            if (url.includes('.m3u8')) {
                mimeType = 'application/x-mpegURL';
            } else if (url.includes('.webm')) {
                mimeType = 'video/webm';
            } else if (url.includes('.avi')) {
                mimeType = 'video/x-msvideo';
            } else if (url.includes('.mkv')) {
                mimeType = 'video/x-matroska';
            }

            playerInstance.source = {
                type: 'video',
                sources: [{
                    src: url,
                    type: mimeType
                }]
            };

            setTimeout(() => {
                if (playerInstance && typeof playerInstance.play === 'function') {
                    enhancedAutoplay(elements.player, playerInstance);
                }
            }, 500);
        }

        async function handleMPDStream(url, servers) {
            try {
                if (window.currentShakaPlayer) {
                    window.currentShakaPlayer.destroy();
                    window.currentShakaPlayer = null;
                }

                updateLoadingStatus('Initializing Shaka Player...');

                if (!window.shaka) {
                    throw new Error('Shaka Player library not loaded');
                }

                if (!window.shakaPlayerInitialized) {
                    shaka.polyfill.installAll();
                    if (!shaka.Player.isBrowserSupported()) {
                        throw new Error('Browser not supported by Shaka Player. MPD streams require Shaka Player.');
                    }
                    window.shakaPlayerInitialized = true;
                    console.log('✅ Shaka Player initialized for MPD stream');
                }

                try {
                    await playWithShaka(url, servers);
                    showPlayingStatus('MPD Stream', 'Playing Successfully');
                    return;
                } catch (shakaError) {
                    console.error('Shaka Player failed:', shakaError);
                    await tryAlternativeMPDSources(url, servers);
                }

            } catch (error) {
                console.error('MPD handling failed:', error);
                updateLoadingStatus('Failed to load MPD stream. Trying alternative sources...', true);

                await tryAlternativeMPDSources(url, servers);
            }
        }

        async function playWithShaka(url, servers) {
            return new Promise(async (resolve, reject) => {
                try {
                    const video = elements.player;
                    const player = new shaka.Player(video);

                    window.currentShakaPlayer = player;

                    player.addEventListener('error', (event) => {
                        console.error('Shaka Player Error:', event.detail);
                        elements.playerMessageArea.textContent = `Error: ${event.detail.message}`;
                        elements.playerMessageArea.style.display = 'block';
                        reject(new Error(event.detail.message));
                    });

                    player.addEventListener('loaded', () => {
                        console.log('✅ MPD stream loaded successfully with Shaka Player');
                        showPlayingStatus('MPD Stream', 'Loaded Successfully');
                    });

                    if (servers && servers.length > 0) {
                        const drmServer = servers.find(s => s.drm || s.license || s.key || s.keyId);

                        if (drmServer) {
                            console.log('🔒 Configuring DRM for channel:', drmServer.name || 'Unknown');

                            if (drmServer.license && drmServer.license.includes(':')) {
                                const [keyId, key] = drmServer.license.split(':');
                                const drmConfiguration = {
                                    servers: {
                                        'com.widevine.alpha': 'https://qp-pldt-live-grp-02-prod.akamaized.net/proxy/widevine'
                                    },
                                    clearKeys: {
                                        [keyId]: key
                                    }
                                };
                                player.configure({ drm: drmConfiguration });
                                console.log('✅ ClearKey DRM configured');
                            } else if (drmServer.license) {
                                const drmConfiguration = {
                                    servers: {
                                        'com.widevine.alpha': drmServer.license
                                    }
                                };
                                player.configure({ drm: drmConfiguration });
                                console.log('✅ License server DRM configured');
                            }
                        } else {
                            console.log('ℹ️ No DRM configuration needed');
                            player.configure({ drm: null });
                        }
                    } else {
                        console.log('ℹ️ No server information, treating as non-DRM');
                        player.configure({ drm: null });
                    }

                    player.configure({
                        streaming: {
                            bufferingGoal: 60,
                            rebufferingGoal: 2,
                            bufferBehind: 30,
                            ignoreTextStreamFailures: true
                        }
                    });

                    await player.load(url);

                    elements.playerMessageArea.style.display = 'none';

                    enhancedAutoplay(video);

                    resolve();

                } catch (error) {
                    reject(error);
                }
            });
        }

        async function playNonDRMMPD(url) {
            try {
                console.log('📺 Playing non-DRM MPD stream:', url);

                if (window.currentShakaPlayer) {
                    window.currentShakaPlayer.destroy();
                    window.currentShakaPlayer = null;
                }

                const video = elements.player;
                const player = new shaka.Player(video);
                window.currentShakaPlayer = player;

                player.addEventListener('error', (event) => {
                    console.error('Shaka Player Error:', event.detail);
                    elements.playerMessageArea.textContent = `Error: ${event.detail.message}`;
                    elements.playerMessageArea.style.display = 'block';
                });

                player.addEventListener('loaded', () => {
                    console.log('✅ Non-DRM MPD stream loaded successfully');
                    showPlayingStatus('Non-DRM MPD Stream', 'Loaded Successfully');
                });

                player.configure({ drm: null });

                player.configure({
                    streaming: {
                        bufferingGoal: 60,
                        rebufferingGoal: 2,
                        bufferBehind: 30,
                        ignoreTextStreamFailures: true
                    }
                });

                await player.load(url);

                console.log('✅ Non-DRM MPD stream loaded successfully');
                elements.playerMessageArea.style.display = 'none';

                enhancedAutoplay(video);

            } catch (error) {
                console.error('Non-DRM MPD playback failed:', error);
                elements.playerMessageArea.textContent = `Error playing stream: ${error.message}`;
                elements.playerMessageArea.style.display = 'block';
                throw error;
            }
        }

        async function tryAlternativeMPDSources(originalUrl, servers) {
            if (!servers || servers.length <= 1) {
                elements.playerMessageArea.textContent = 'No alternative sources available.';
                return;
            }

            for (const server of servers) {
                if (server.url !== originalUrl && server.url.includes('.mpd')) {
                    try {
                        updateLoadingStatus(`Trying alternative source: ${server.name || 'Unknown'}`);
                        await handleMPDStream(server.url, servers);
                        return;
                    } catch (error) {
                        console.warn('Alternative source failed:', server.url, error);
                        continue;
                    }
                }
            }

            updateLoadingStatus('All MPD sources failed. Please try again later.', true);
        }

        async function detectDRMProtection(mpdUrl) {
            try {
                const response = await fetch(mpdUrl);
                const manifest = await response.text();

                const hasWidevine = manifest.includes('urn:uuid:EDEF8BA9-79D6-4ACE-A3C8-27DCD51D21ED');
                const hasPlayReady = manifest.includes('urn:uuid:9A04F079-9840-4286-AB92-E65BE0885F95');
                const hasFairPlay = manifest.includes('urn:uuid:94CE86FB-07FF-4F43-ADB8-93D2FA968CA2');

                return {
                    hasDRM: hasWidevine || hasPlayReady || hasFairPlay,
                    widevine: hasWidevine,
                    playReady: hasPlayReady,
                    fairPlay: hasFairPlay
                };
            } catch (error) {
                console.warn('Could not detect DRM protection:', error);
                return { hasDRM: false };
            }
        }

        function detectChannelFormat(url) {
            const formatInfo = {
                type: 'unknown',
                isLive: false,
                hasDRM: false,
                quality: 'unknown',
                recommendedPlayer: 'unknown'
            };

            if (url.includes('.mpd')) {
                formatInfo.type = 'MPD';
                formatInfo.recommendedPlayer = 'Shaka Player';
            } else if (url.includes('.m3u8')) {
                formatInfo.type = 'HLS';
                formatInfo.recommendedPlayer = 'Plyr (HLS.js)';
            } else if (url.includes('.mp4')) {
                formatInfo.type = 'MP4';
                formatInfo.recommendedPlayer = 'Plyr';
            } else if (url.includes('.webm')) {
                formatInfo.type = 'WebM';
                formatInfo.recommendedPlayer = 'Plyr';
            } else if (url.includes('.avi')) {
                formatInfo.type = 'AVI';
                formatInfo.recommendedPlayer = 'Plyr';
            } else if (url.includes('.mkv')) {
                formatInfo.type = 'MKV';
                formatInfo.recommendedPlayer = 'Plyr';
            }

            if (url.includes('live') || url.includes('stream') || url.includes('tv') ||
                url.includes('broadcast') || url.includes('channel')) {
                formatInfo.isLive = true;
            }

            if (url.includes('1080p') || url.includes('1080')) {
                formatInfo.quality = '1080p';
            } else if (url.includes('720p') || url.includes('720')) {
                formatInfo.quality = '720p';
            } else if (url.includes('480p') || url.includes('480')) {
                formatInfo.quality = '480p';
            } else if (url.includes('360p') || url.includes('360')) {
                formatInfo.quality = '360p';
            }

            console.log('🔍 Channel format detected:', formatInfo);
            return formatInfo;
        }

        function showPlayingStatus(channelName, status = 'Playing') {
            if (elements.playerMessageArea) {
                elements.playerMessageArea.textContent = `${status}: ${channelName}`;
                elements.playerMessageArea.style.display = 'block';
                elements.playerMessageArea.style.color = '#4CAF50';
                elements.playerMessageArea.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
            }
        }

        function showPlayButton() {
            if (elements.playerMessageArea) {
                elements.playerMessageArea.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        <p>Click to start playback:</p>
                        <button onclick="startPlayback()" style="
                            background: #ff5722;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            font-size: 16px;
                        ">▶️ Play</button>
                    </div>
                `;
                elements.playerMessageArea.style.display = 'block';
                elements.playerMessageArea.style.color = '#ffffff';
                elements.playerMessageArea.style.backgroundColor = 'rgba(255, 87, 34, 0.1)';
            }
        }

        function startPlayback() {
            if (window.currentShakaPlayer) {
                const video = elements.player;
                video.play().catch(error => console.log('Manual play failed:', error));
            } else if (playerInstance) {
                playerInstance.play().catch(error => console.log('Manual play failed:', error));
            }
        }

        function enhancedAutoplay(videoElement, playerInstance = null) {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            if (isMobile) {
                videoElement.muted = true;
                videoElement.play().then(() => {
                    console.log('✅ Mobile muted autoplay successful');
                    setTimeout(() => {
                        videoElement.muted = false;
                    }, 100);
                }).catch(error => {
                    console.log('Mobile muted autoplay failed:', error);
                    showPlayButton();
                });
            } else {
                videoElement.play().then(() => {
                    console.log('✅ Desktop autoplay successful');
                }).catch(error => {
                    console.log('Desktop autoplay failed:', error);
                    showPlayButton();
                });
            }
        }

        function selectOptimalServer(servers) {
            if (!servers || servers.length === 0) return null;

            servers.forEach(server => {
                if (server.url.includes('.mpd')) {
                    if (server.drm || server.license || server.key || server.keyId) {
                        server.hasDRMConfig = true;
                        console.log(`🔒 Server has DRM config: ${server.name || 'Unknown'}`);
                    } else {
                        server.hasDRMConfig = false;
                        console.log(`🔓 Server has no DRM config: ${server.name || 'Unknown'}`);
                    }

                    server.isMPD = true;
                }
            });

            const sortedServers = servers.sort((a, b) => {
                if (a.isMPD && !b.isMPD) return -1;
                if (!a.isMPD && b.isMPD) return 1;

                if (a.hasDRMConfig && !b.hasDRMConfig) return -1;
                if (!a.hasDRMConfig && b.hasDRMConfig) return 1;

                const aQuality = parseInt(a.name?.replace(/\D/g, '') || '0');
                const bQuality = parseInt(b.name?.replace(/\D/g, '') || '0');
                if (aQuality !== bQuality) return bQuality - aQuality;

                const aInfo = Object.keys(a).length;
                const bInfo = Object.keys(b).length;
                if (aInfo !== bInfo) return bInfo - aInfo;

                return 0;
            });

            const selectedServer = sortedServers[0];
            console.log(`🎯 Selected optimal server: ${selectedServer.name || 'Unknown'} (MPD: ${selectedServer.isMPD}, DRM: ${selectedServer.hasDRMConfig})`);

            return selectedServer;
        }

        function updateServerDropdown(servers, currentServer = null) {
            if (!elements.serverSelector || !elements.serverSelectorContainer) return;

            const existingUniversal = document.getElementById('universal-server-selector');
            if (existingUniversal && existingUniversal.parentNode) {
                existingUniversal.parentNode.removeChild(existingUniversal);
            }
            const existingEmbedded = document.getElementById('embedded-server-selector');
            if (existingEmbedded && existingEmbedded.parentNode) {
                existingEmbedded.parentNode.removeChild(existingEmbedded);
            }

            elements.serverSelector.innerHTML = '';

            if (!servers || servers.length <= 1) {
                elements.serverSelectorContainer.style.display = 'none';
                return;
            }

            servers.forEach((s, idx) => {
                const option = document.createElement('option');
                option.value = s.url;
                option.textContent = s.name || `Server ${idx + 1}`;
                if (currentServer && (s.url === currentServer.url)) {
                    option.selected = true;
                }
                elements.serverSelector.appendChild(option);
            });

            elements.serverSelectorContainer.style.display = 'block';

            elements.serverSelector.onchange = (e) => {
                const selectedUrl = e.target.value;
                const selected = servers.find(s => s.url === selectedUrl);
                if (selected) {
                    switchToServer(selected, servers);
                }
            };
        }

        async function switchToServer(server, allServers) {
            try {
                if (playerInstance) {
                    playerInstance.pause();
                    playerInstance.stop();
                }

                if (window.currentShakaPlayer) {
                    window.currentShakaPlayer.destroy();
                    window.currentShakaPlayer = null;
                }

                const playerContainer = document.querySelector('.player-container');
                const existingIframe = playerContainer.querySelector('iframe.external-content-iframe');
                if (existingIframe) {
                    existingIframe.remove();
                }

                elements.player.style.display = 'block';

                const isEmbed = isVidsrcUrl(server.url) || isVidjoyUrl(server.url) ||
                               isVidsrcMeUrl(server.url) || isVidsrcToUrl(server.url) ||
                               isVidsrcXyzUrl(server.url) || isGoDrivePlayerUrl(server.url) ||
                               isVidLinkProUrl(server.url) || is2EmbedUrl(server.url) ||
                               isEmbedSuUrl(server.url) || isAutoEmbedUrl(server.url) ||
                               isVideasyUrl(server.url) || isMegaNzUrl(server.url) ||
                               isGoogleDriveUrl(server.url);

                if (isEmbed) {
                    elements.player.style.display = 'none';

                    const iframe = document.createElement('iframe');
                    iframe.className = 'external-content-iframe';
                    iframe.style.cssText = `
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        border: none;
                    `;
                    iframe.setAttribute('allowfullscreen', '');
                    iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');

                    let embedUrl = server.url;
                    if (isGoogleDriveUrl(server.url)) {
                        const fileId = extractGoogleDriveId(server.url);
                        if (fileId) embedUrl = `https://drive.google.com/file/d/${fileId}/preview`;
                    } else if (isMegaNzUrl(server.url)) {
                        embedUrl = server.url.replace('/file/', '/embed/');
                    }

                    iframe.src = getAutoplayUrl(embedUrl);
                    playerContainer.appendChild(iframe);
                } else {
                    await playUrl(server.url, allServers);
                }

                window.currentServer = server;
                if (elements.serverSelector) {
                    elements.serverSelector.value = server.url;
                }

                showServerSwitchFeedback(server.name || `Server ${allServers.indexOf(server) + 1}`);

            } catch (error) {
                console.error('Error switching to server:', error);
                showServerSwitchFeedback('Error switching server', 'error');
            }
        }

        function isYouTubeUrl(url) {
            if (!url) return false;
            return url.includes('youtube.com') || url.includes('youtu.be');
        }

        function isVidsrcUrl(url) {
            if (!url) return false;
            return url.includes('vidsrc.net/embed') || url.includes('vidsrc.me/embed') || url.includes('vidsrc.pro/embed') || url.includes('vidsrc.win/embed') ||
                   url.includes('vidsrc.me') || url.includes('vidsrc.to') || url.includes('vidsrc.xyz') || url.includes('vidsrc.win') ||
                   url.includes('godriveplayer') || url.includes('vidlink.pro') || url.includes('2embed.cc') ||
                   url.includes('embed.su') || url.includes('autoembed.cc') || url.includes('vidfast.pro');
        }

        function isVidjoyUrl(url) {
            if (!url) return false;
            return url.includes('vidjoy.pro/embed');
        }

        function isVidsrcMeUrl(url) {
            if (!url) return false;
            return url.includes('vidsrc.me');
        }

        function isVidsrcToUrl(url) {
            if (!url) return false;
            return url.includes('vidsrc.to');
        }

        function isVidsrcXyzUrl(url) {
            if (!url) return false;
            return url.includes('vidsrc.xyz');
        }

        function isGoDrivePlayerUrl(url) {
            if (!url) return false;
            return url.includes('godriveplayer');
        }

        function isVidLinkProUrl(url) {
            if (!url) return false;
            return url.includes('vidlink.pro');
        }

        function is2EmbedUrl(url) {
            if (!url) return false;
            return url.includes('2embed.cc');
        }

        function isEmbedSuUrl(url) {
            if (!url) return false;
            return url.includes('embed.su');
        }

        function isAutoEmbedUrl(url) {
            if (!url) return false;
            return url.includes('autoembed.cc');
        }

        function isVideasyUrl(url) {
            if (!url) return false;
            return url.includes('videasy');
        }

        function isVidfastEmbedUrl(url) {
            if (!url) return false;
            return url.includes('vidfast.net') || url.includes('vidfast.to');
        }

        function isVidplusUrl(url) {
            if (!url) return false;
            return url.includes('vidplus.to');
        }

        function isMegaNzUrl(url) {
            if (!url) return false;
            return url.includes('mega.nz/file');
        }

        function isGoogleDriveUrl(url) {
            if (!url) return false;
            return url.includes('drive.google.com') || url.includes('drive.usercontent.google.com');
        }

        function extractYouTubeId(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        function extractGoogleDriveId(url) {
            let id = null;
            if (url.includes('drive.google.com')) {
                const match = url.match(/file\/d\/([^/]+)/);
                if (match) {
                    id = match[1];
                }
            } else if (url.includes('drive.usercontent.google.com')) {
                const match = url.match(/id=([^&]+)/);
                if (match) {
                    id = match[1];
                }
            }
            return id;
        }

        async function updatePlayerSource(servers) {
            if (!playerInstance) return;

            if (servers && servers.length > 0) {
                const optimalServer = selectOptimalServer(servers);

                if (optimalServer) {
                    if (isVidsrcUrl(optimalServer.url) || isVidjoyUrl(optimalServer.url) || isVidsrcMeUrl(optimalServer.url) || isVidsrcToUrl(optimalServer.url) || isVidsrcXyzUrl(optimalServer.url) || isGoDrivePlayerUrl(optimalServer.url) || isVidLinkProUrl(optimalServer.url) || is2EmbedUrl(optimalServer.url) || isEmbedSuUrl(optimalServer.url) || isAutoEmbedUrl(optimalServer.url) || isVideasyUrl(optimalServer.url) || isMegaNzUrl(optimalServer.url) || isGoogleDriveUrl(optimalServer.url)) {
                        playUrl(optimalServer.url, servers);
                        return;
                    }

                    if (optimalServer.url.includes('.mpd')) {
                        await handleMPDStream(optimalServer.url, servers);
                        return;
                    }

                    const sources = servers.map(server => ({
                        src: server.url,
                        size: parseInt(server.name?.replace(/\D/g, '') || '0'),
                        type: server.url.includes('m3u8') ? 'application/x-mpegURL' : 'video/mp4'
                    }));

                    playerInstance.source = {
                        type: 'video',
                        sources: sources,
                    };

                    addStretchButtonToPlayer();

                    updateServerDropdown(servers, optimalServer);

                    playerInstance.on('error', async (event) => {
                        console.log('Player error detected, trying next server...');
                        const currentIndex = servers.findIndex(s => s.url === optimalServer.url);
                        await tryNextServer(servers, currentIndex);
                    });
                }
            }
        }

        function openSeason(season) {
            currentSeason = season;

            elements.episodeSelector.innerHTML = '<option value="">Select Episode</option>';
            season.Episodes.forEach(ep => {
                const option = document.createElement('option');
                option.value = ep.Episode;
                option.textContent = `Episode ${ep.Episode}: ${ep.Title}`;
                elements.episodeSelector.appendChild(option);
            });

            elements.episodeSelectorContainer.style.display = 'block';

            if (season.Episodes.length > 0) {
                const firstEpisode = season.Episodes[0];
                elements.episodeSelector.value = firstEpisode.Episode;
                playEpisode(firstEpisode, true);
            } else {
                currentEpisode = null;
                updateNavigationButtonsState();
            }
        }

        function playEpisode(episode, autoplay = false) {
            currentEpisode = episode;
            elements.viewerTitle.textContent = `${currentSeries.Title} - ${episode.Title}`;
            elements.viewerDescription.textContent = episode.Description || currentSeries.Description;

            if (episode.Servers) {
                updatePlayerSource(episode.Servers);
            }

            if (elements.episodeSelector && episode.Episode !== undefined) {
                elements.episodeSelector.value = episode.Episode;
            }

            if (currentSeries && currentSeason) {
                const episodeState = {
                    page: 'viewer',
                    contentId: currentSeries.Title,
                    season: currentSeason.Season,
                    episode: episode.Episode
                };
                history.pushState(episodeState, `Episode ${episode.Episode}`, `#episode-${episode.Episode}`);
            }

            updateNavigationButtonsState();

            if (autoplay) {
                setTimeout(() => {
                    if (playerInstance && typeof playerInstance.play === 'function') {
                        enhancedAutoplay(elements.player, playerInstance);
                    }
                }, 1000);
            }
        }

        function closeViewerInternalLogic() {
            document.querySelector('.carousel').style.display = 'block';
            document.querySelector('.filters-section').style.display = 'block';
            document.querySelector('.content-container').style.display = 'block';
            elements.viewerPage.style.display = 'none';

            if (playerInstance) {
                playerInstance.pause();
                playerInstance.stop();
            }

            if (window.currentShakaPlayer) {
                try {
                    window.currentShakaPlayer.destroy();
                    window.currentShakaPlayer = null;
                } catch (error) {
                    console.log('Error destroying Shaka Player:', error);
                }
            }

            if (dashPlayer) {
                dashPlayer.reset();
                dashPlayer = null;
            }

            if (playerInstance && playerInstance.source) {
                playerInstance.source = null;
            }

            const playerContainer = document.querySelector('.player-container');
            if (playerContainer) {
                const externalIframe = playerContainer.querySelector('iframe.external-content-iframe');
                if (externalIframe) {
                    externalIframe.src = 'about:blank';
                    externalIframe.remove();
                }
            }
            elements.player.style.display = 'block';

            if (elements.videoDetailsContainer) {
                elements.videoDetailsContainer.appendChild(elements.episodeSelectorContainer);
                elements.videoDetailsContainer.appendChild(elements.seasonSelector);
            }
            elements.episodeSelectorContainer.style.display = 'none';
            elements.seasonSelector.style.display = 'none';

            currentEpisode = null;
            currentSeason = null;
            currentSeries = null;
            currentContentInfo = {};

            if (elements.prevEpisodeBtn && elements.nextEpisodeBtn) {
                elements.prevEpisodeBtn.style.display = 'none';
                elements.nextEpisodeBtn.style.display = 'none';
            }

            if (elements.playerMessageArea) {
                elements.playerMessageArea.style.display = 'none';
                elements.playerMessageArea.textContent = '';
            }

            window.scrollTo(0, 0);
            elements.backButton.focus();
        }

        function closeViewer() {
            closeViewerInternalLogic();
        }

        function setupMobileBackButton() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            if (isMobile) {
                const backText = document.querySelector('.back-text');
                const mobileHint = document.querySelector('.mobile-back-hint');
                if (backText && mobileHint) {
                    backText.style.display = 'none';
                    mobileHint.style.display = 'inline-block';
                }
            }
            window.addEventListener('popstate', function(event) {
                if (elements.viewerPage.style.display !== 'none') {
                    if (event.state && event.state.page === 'browse') {
                        closeViewerInternalLogic();
                    } else if (event.state && event.state.page === 'viewer' && event.state.episode) {
                        handleEpisodeBackNavigation(event.state);
                    } else {
                        closeViewerInternalLogic();
                    }
                }
            });

            if (navigator.userAgent.includes('Android')) {
                document.addEventListener('backbutton', function() {
                    if (elements.viewerPage.style.display !== 'none') {
                        if (currentEpisode && currentSeason && currentSeries) {
                            const currentEpisodeIndex = currentSeason.Episodes.findIndex(ep => ep.Episode === currentEpisode.Episode);
                            if (currentEpisodeIndex > 0) {
                                const previousEpisode = currentSeason.Episodes[currentEpisodeIndex - 1];
                                playEpisode(previousEpisode, true);
                                return;
                            }
                        }

                        closeViewerInternalLogic();
                    }
                });
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && elements.viewerPage.style.display !== 'none') {
                    closeViewerInternalLogic();
                }
            });
        }

        function handleEpisodeBackNavigation(state) {
            if (state.season && state.episode && currentSeason) {
                const targetEpisode = currentSeason.Episodes.find(ep => ep.Episode === state.episode);
                if (targetEpisode) {
                    playEpisode(targetEpisode, true);
                    return;
                }
            }
            closeViewerInternalLogic();
        }

        function setupEventListeners() {
            const refreshBtn = document.getElementById('refresh-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', async () => {
                    if (!confirm("Are you sure you want to clear the local cache and refresh all data from the server? This may take a few minutes.")) {
                        return;
                    }

                    try {
                        console.log("Clearing cache and reloading...");
                        const db = await dbUtil.open();
                        await dbUtil.clear(db);
                        db.close();

                        const notificationBar = document.getElementById('notification-bar');
                        const notificationMessage = document.getElementById('notification-message');
                        notificationMessage.textContent = "Cache cleared successfully! Reloading the latest data from the server...";
                        notificationBar.style.display = 'flex';
                        setTimeout(() => {
                            const header = document.querySelector('header');
                            if (notificationBar.offsetHeight > 0) {
                                header.style.top = `${notificationBar.offsetHeight}px`;
                            }
                        }, 100);

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);

                    } catch (error) {
                        console.error("Failed to clear cache:", error);
                        alert("There was an error clearing the cache. Please try clearing your browser's site data manually.");
                    }
                });
            }

            elements.themeToggle.addEventListener('click', toggleTheme);

            elements.viewToggleBtn.addEventListener('click', () => {
                const icon = elements.viewToggleBtn.querySelector('i');
                if (currentView === 'grid') {
                    currentView = 'list';
                    icon.classList.remove('fa-th');
                    icon.classList.add('fa-list');
                } else {
                    currentView = 'grid';
                    icon.classList.remove('fa-list');
                    icon.classList.add('fa-th');
                }
                renderCurrentView();
                setupLazyLoading();
            });

            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const category = item.dataset.category;

                    if (elements.viewerPage.style.display === 'block') {
                        closeViewerInternalLogic();
                        history.replaceState({ page: 'browse' }, 'Browse Content', window.location.pathname + window.location.search);
                    }

                    await renderContent(category);

                    navItems.forEach(nav => nav.classList.remove('active'));
                    item.classList.add('active');
                });
            });

            elements.genreFilter.addEventListener('change', () => {
                const activeNavItem = document.querySelector('.nav-item.active');
                const category = activeNavItem ? activeNavItem.dataset.category : 'all';
                renderContent(category);
            });

            elements.countryFilter.addEventListener('change', () => {
                const activeNavItem = document.querySelector('.nav-item.active');
                const category = activeNavItem ? activeNavItem.dataset.category : 'all';
                renderContent(category);
            });

            elements.yearFilter.addEventListener('change', () => {
                const activeNavItem = document.querySelector('.nav-item.active');
                const category = activeNavItem ? activeNavItem.dataset.category : 'all';
                renderContent(category);
            });

            elements.sortFilter.addEventListener('change', () => {
                const activeNavItem = document.querySelector('.nav-item.active');
                const category = activeNavItem ? activeNavItem.dataset.category : 'all';
                renderContent(category);
            });

            elements.searchInput.addEventListener('input', debounce(handleSearch, 300));

            elements.closeSearchBtn.addEventListener('click', function() {
                elements.searchInput.value = '';
                elements.searchResults.style.display = 'none';
                elements.searchInput.setAttribute('aria-expanded', 'false');
                if (elements.searchContainer) {
                    elements.searchContainer.classList.remove('show');
                }
            });

            elements.carouselPrev.addEventListener('click', () => {
                currentCarouselIndex = (currentCarouselIndex - 1 + elements.carouselInner.children.length) %
                    elements.carouselInner.children.length;
                updateCarousel();
            });

            elements.carouselNext.addEventListener('click', () => {
                currentCarouselIndex = (currentCarouselIndex + 1) % elements.carouselInner.children.length;
                updateCarousel();
            });

            elements.mobileSearchBtn.addEventListener('click', () => {
                elements.searchContainer.classList.toggle('show');
                if (elements.searchContainer.classList.contains('show')) {
                    elements.searchInput.focus();
                } else {
                    elements.searchResults.style.display = 'none';
                    elements.searchInput.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('click', (event) => {
                const isMobile = window.innerWidth <= 768;
                if (isMobile && elements.searchContainer.classList.contains('show')) {
                    if (!elements.searchContainer.contains(event.target) &&
                        !elements.mobileSearchBtn.contains(event.target)) {
                        elements.searchContainer.classList.remove('show');
                        elements.searchResults.style.display = 'none';
                        elements.searchInput.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    elements.header.classList.add('scrolled');
                } else {
                    elements.header.classList.remove('scrolled');
                }
            });

            elements.backButton.addEventListener('click', function() {
                closeViewerInternalLogic();
                history.pushState({ page: 'browse' }, 'Browse Content', window.location.pathname + window.location.search);
            });

            if ('ontouchstart' in window) {
                elements.backButton.addEventListener('touchstart', function(e) {
                    this.style.transform = 'scale(0.95)';
                });

                elements.backButton.addEventListener('touchend', function(e) {
                    this.style.transform = 'scale(1)';
                    setTimeout(() => {
                        history.back();
                    }, 100);
                });

                elements.backButton.addEventListener('touchmove', function(e) {
                    e.preventDefault();
                });
            }

            elements.episodeSelector.addEventListener('change', function() {
                const episodeNum = parseInt(this.value);
                if (!currentSeason) return;

                const episode = currentSeason.Episodes.find(ep => ep.Episode === episodeNum);
                if (episode) {
                    playEpisode(episode);
                }
            });

            window.addEventListener('popstate', function(event) {
                const state = event.state;

                if (elements.viewerPage.style.display === 'block') {
                    if (!state || state.page !== 'viewer') {
                        closeViewerInternalLogic();
                        if (!state || state.page === 'browse') {
                             history.replaceState({ page: 'browse' }, 'Browse Content', window.location.pathname + window.location.search);
                        }
                    }
                } else if (state && state.page === 'viewer' && window.location.hash === '#viewer') {
                    const contentToOpen = findContentById(state.contentId);
                    if (contentToOpen) {
                        openViewer(contentToOpen);
                    } else {
                        history.replaceState({ page: 'browse' }, 'Browse Content', window.location.pathname + window.location.search);
                    }
                }
            });

            if (elements.prevEpisodeBtn) {
                elements.prevEpisodeBtn.addEventListener('click', handlePrevEpisode);
            }
            if (elements.nextEpisodeBtn) {
                elements.nextEpisodeBtn.addEventListener('click', handleNextEpisode);
            }

            window.addEventListener('scroll', () => {
                if (isFetching) return;

                const scrollPosition = window.innerHeight + window.scrollY;
                const documentHeight = document.documentElement.scrollHeight;

                if (scrollPosition >= documentHeight - LAZY_LOAD_THRESHOLD) {
                    if (currentPage < totalPages) {
                        loadMoreContent();
                    }
                }
            });

            elements.hamburgerBtn.addEventListener('click', () => {
                elements.mobileFiltersMenu.classList.toggle('show');
            });

            elements.stretchBtn.addEventListener('click', () => {
                toggleStretch();
            });
        }

        function addStretchButtonToPlayer() {
            if (!playerInstance || !playerInstance.elements || !playerInstance.elements.controls) {
                return;
            }

            const controlsContainer = playerInstance.elements.controls;
            const settingsButton = controlsContainer.querySelector('button[data-plyr="settings"]');

            let stretchButton = controlsContainer.querySelector('button[aria-label="Stretch"]');
            if (!stretchButton) {
                stretchButton = document.createElement('button');
                stretchButton.type = 'button';
                stretchButton.className = 'plyr__controls__item plyr__control';
                stretchButton.innerHTML = '<i class="fas fa-expand-arrows-alt"></i>';
                stretchButton.setAttribute('aria-label', 'Stretch');

                stretchButton.addEventListener('click', () => {
                    toggleStretch();
                    stretchButton.classList.toggle('active');
                });

                if (settingsButton) {
                    settingsButton.parentNode.insertBefore(stretchButton, settingsButton);
                } else {
                    controlsContainer.appendChild(stretchButton);
                }
            }

            const videoElement = playerInstance.elements.container.querySelector('video');
            if (videoElement) {
                videoElement.style.objectFit = isStretched ? 'fill' : 'contain';
            }
            if (isStretched) {
                stretchButton.classList.add('active');
            } else {
                stretchButton.classList.remove('active');
            }
        }

        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            const icon = elements.themeToggle.querySelector('i');
            if (document.body.classList.contains('light-theme')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                document.body.style.backgroundColor = '#f0f0f0';
                document.body.style.color = '#333';
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                document.body.style.backgroundColor = '#0f0f0f';
                document.body.style.color = '#f5f5f5';
            }
        }

        function debounce(func, wait) {
          let timeout;
          return function(...args) {
            const later = () => {
              clearTimeout(timeout);
              func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
          };
        }

        function handleSearch() {
            const query = elements.searchInput.value.toLowerCase();
            elements.searchResults.innerHTML = '';

            elements.searchInput.setAttribute('aria-expanded', 'false');

            if (query.length < 2) {
                elements.searchResults.style.display = 'none';
                return;
            }

            const dataLoaded = (cineData && cineData.Categories && cineData.Categories.length > 0) ||
                               (Array.isArray(cachedContent) && cachedContent.length > 0);

            if (!dataLoaded) {
                const message = document.createElement('div');
                message.className = 'search-message';
                message.setAttribute('role', 'status');
                message.textContent = 'Search unavailable. Content is still loading.';
                elements.searchResults.appendChild(message);
                elements.searchResults.style.display = 'block';
                elements.searchInput.setAttribute('aria-expanded', 'true');
                return;
            }

            const results = [];

            cachedContent.forEach(item => {
                if (item.Title.toLowerCase().includes(query)) {
                    results.push({
                        title: item.Title,
                        type: item.type === 'movie' ? 'Movie' :
                              item.type === 'series' ? 'TV Series' : 'Live TV',
                        thumbnail: item.Thumbnail || item.Poster,
                        year: item.Year || ''
                    });
                }
            });

            if (results.length > 0) {
                results.slice(0, 5).forEach(result => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.setAttribute('role', 'option');
                    resultItem.setAttribute('aria-selected', 'false');
                    resultItem.innerHTML = `
                        <img src="${result.thumbnail}" alt="${result.title}" onerror="this.onerror=null; this.src='${PLACEHOLDER_IMAGE_URL}';">
                        <div class="search-result-info">
                            <h4>${result.title}</h4>
                            <p>${result.type} • ${result.year || ''}</p>
                        </div>
                    `;
                    resultItem.addEventListener('click', () => {
                        const found = cachedContent.find(item => item.Title === result.title);
                        if (found) {
                            openViewer(found);
                        }
                        elements.searchResults.style.display = 'none';
                        elements.searchInput.setAttribute('aria-expanded', 'false');
                    });
                    elements.searchResults.appendChild(resultItem);
                });
                elements.searchResults.style.display = 'block';
                elements.searchInput.setAttribute('aria-expanded', 'true');
            } else {
                const message = document.createElement('div');
                message.className = 'search-message';
                message.setAttribute('role', 'status');
                message.textContent = `No results for "${elements.searchInput.value}"`;
                elements.searchResults.appendChild(message);
                elements.searchResults.style.display = 'block';
                elements.searchInput.setAttribute('aria-expanded', 'true');
            }
        }

        function updateCarousel() {
            elements.carouselInner.style.transform = `translateX(-${currentCarouselIndex * 100}%)`;

            document.querySelectorAll('.indicator').forEach((indicator, index) => {
                if (index === currentCarouselIndex) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
        }

        const ALL_RATINGS = {
            "Movies": ["G", "PG", "PG-13", "R", "NC-17"],
            "TV Shows": ["TV-Y", "TV-Y7", "TV-G", "TV-PG", "TV-14", "TV-MA"],
            "Philippines": ["SPG", "R-13", "R-16", "R-18"]
        };

        function setupParentalControls() {
            loadParentalControls();
            generatePinPad(elements.pinPad, handlePinPadClick);
            generatePinPad(elements.pinPadInput, handlePinEntryPinPadClick);
            generateRatingsCheckboxes();
            attachParentalEventListeners();
            updateParentalControlsUI();
        }

        function loadParentalControls() {
            const savedSettings = JSON.parse(localStorage.getItem('parentalControls'));
            if (savedSettings) {
                parentalControls = savedSettings;
                if (!Array.isArray(parentalControls.allowedRatings)) {
                    parentalControls.allowedRatings = [...ALL_RATINGS.Movies, ...ALL_RATINGS["TV Shows"], ...ALL_RATINGS.Philippines];
                }
            } else {
                parentalControls.allowedRatings = [...ALL_RATINGS.Movies, ...ALL_RATINGS["TV Shows"], ...ALL_RATINGS.Philippines];
            }
        }

        function saveParentalControls() {
            localStorage.setItem('parentalControls', JSON.stringify(parentalControls));
            updateParentalControlsUI();
        }

        function generatePinPad(container, handler) {
            container.innerHTML = '';
            const buttons = [1, 2, 3, 4, 5, 6, 7, 8, 9, 'backspace', 0, ''];
            buttons.forEach(val => {
                const btn = document.createElement('button');
                btn.className = 'pin-btn';
                if (typeof val === 'number') {
                    btn.textContent = val;
                    btn.addEventListener('click', () => handler(val));
                } else if (val === 'backspace') {
                    btn.innerHTML = '<i class="fas fa-backspace"></i>';
                    btn.classList.add('backspace');
                    btn.addEventListener('click', () => handler('backspace'));
                } else {
                    btn.style.visibility = 'hidden';
                }
                container.appendChild(btn);
            });
        }

        function generateRatingsCheckboxes() {
            const container = elements.ratingsCheckboxContainer;
            container.innerHTML = '';
            for (const category in ALL_RATINGS) {
                const title = document.createElement('h4');
                title.className = 'rating-category-title';
                title.textContent = category;
                container.appendChild(title);

                ALL_RATINGS[category].forEach(rating => {
                    const label = document.createElement('label');
                    label.className = 'rating-checkbox-label';
                    label.textContent = rating;

                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.value = rating;
                    input.checked = parentalControls.allowedRatings.includes(rating);

                    const span = document.createElement('span');
                    span.className = 'checkmark';

                    label.appendChild(input);
                    label.appendChild(span);
                    container.appendChild(label);
                });
            }
        }

        function updatePinDisplay(displayElement, pin) {
            const dots = displayElement.querySelectorAll('.pin-dot');
            dots.forEach((dot, index) => {
                if (index < pin.length) {
                    dot.classList.add('filled');
                } else {
                    dot.classList.remove('filled');
                }
            });
        }

        function updateParentalControlsUI() {
            elements.unratedContentToggle.checked = parentalControls.allowUnrated;
            if (parentalControls.pin) {
                elements.pinStatusText.textContent = "PIN is set. To change it, reset it first.";
            } else {
                elements.pinStatusText.textContent = "Enter a new 4-digit PIN.";
            }
            const allRatings = [...ALL_RATINGS.Movies, ...ALL_RATINGS["TV Shows"], ...ALL_RATINGS.Philippines];
            if (parentalControls.allowedRatings.length >= allRatings.length) {
                elements.allowedRatingsDisplay.textContent = 'All ratings allowed';
            } else if (parentalControls.allowedRatings.length === 0) {
                elements.allowedRatingsDisplay.textContent = 'No ratings allowed';
            } else {
                elements.allowedRatingsDisplay.textContent = parentalControls.allowedRatings.join(', ');
            }
        }

        function showModal(modalElement) {
            modalElement.style.display = 'flex';
        }

        function hideModal(modalElement) {
            modalElement.style.display = 'none';
        }

        function openParentalControls() {
            if (parentalControls.pin) {
                elements.pinEntryTitle.textContent = "Enter PIN to Open Settings";
                showModal(elements.pinEntryModal);
                pinEntryCallback = () => {
                    hideModal(elements.pinEntryModal);
                    showModal(elements.parentalControlsModal);
                };
            } else {
                showModal(elements.parentalControlsModal);
            }
        }

        function handlePinPadClick(value) {
            if (parentalControls.pin) {
                elements.pinStatusText.textContent = "PIN is already set. Reset it to change.";
                return;
            }

            if (value === 'backspace') {
                currentPinInput = currentPinInput.slice(0, -1);
            } else if (currentPinInput.length < 4) {
                currentPinInput += value;
            }

            updatePinDisplay(elements.pinDisplay, currentPinInput);

            if (currentPinInput.length === 4) {
                if (!isSettingPin) {
                    isSettingPin = true;
                    tempPin = currentPinInput;
                    elements.pinStatusText.textContent = 'Confirm your new PIN.';
                    currentPinInput = '';
                    setTimeout(() => updatePinDisplay(elements.pinDisplay, ''), 200);
                } else {
                    if (currentPinInput === tempPin) {
                        parentalControls.pin = tempPin;
                        saveParentalControls();
                        elements.pinStatusText.textContent = 'PIN set successfully!';
                        isSettingPin = false;
                        tempPin = '';
                        currentPinInput = '';
                         setTimeout(() => {
                           hideModal(elements.parentalControlsModal);
                        }, 1500);
                    } else {
                        elements.pinStatusText.textContent = 'PINs do not match. Try again.';
                        isSettingPin = false;
                        tempPin = '';
                        currentPinInput = '';
                        setTimeout(() => {
                            updatePinDisplay(elements.pinDisplay, '');
                            elements.pinStatusText.textContent = 'Enter a new 4-digit PIN.';
                        }, 2000);
                    }
                }
            }
        }

        function handlePinEntryPinPadClick(value) {
            if (value === 'backspace') {
                currentPinInput = currentPinInput.slice(0, -1);
            } else if (currentPinInput.length < 4) {
                currentPinInput += value;
            }
            updatePinDisplay(elements.pinDisplayInput, currentPinInput);
        }

        function verifyPin() {
            if (currentPinInput === parentalControls.pin) {
                elements.pinStatusTextInput.textContent = 'PIN Correct!';
                setTimeout(() => {
                    hideModal(elements.pinEntryModal);
                    if (pinEntryCallback) {
                        pinEntryCallback();
                    }
                    resetPinEntry();
                }, 500);
            } else {
                elements.pinStatusTextInput.textContent = 'Incorrect PIN. Please try again.';
                currentPinInput = '';
                setTimeout(() => {
                    updatePinDisplay(elements.pinDisplayInput, '');
                }, 1000);
            }
        }

        function resetPinEntry() {
            currentPinInput = '';
            pinEntryCallback = null;
            updatePinDisplay(elements.pinDisplayInput, '');
            elements.pinStatusTextInput.textContent = '';
        }

        function handleSaveRatings() {
            const selectedRatings = [];
            const checkboxes = elements.ratingsCheckboxContainer.querySelectorAll('input[type="checkbox"]:checked');
            checkboxes.forEach(checkbox => {
                selectedRatings.push(checkbox.value);
            });
            parentalControls.allowedRatings = selectedRatings;
            saveParentalControls();
            hideModal(elements.ratingsSelectModal);
        }

        function attachParentalEventListeners() {
            elements.parentalControlsLink.addEventListener('click', (e) => {
                e.preventDefault();
                openParentalControls();
            });
            elements.closeParentalControlsModal.addEventListener('click', () => hideModal(elements.parentalControlsModal));

            elements.changeRatingsBtn.addEventListener('click', () => {
                generateRatingsCheckboxes();
                showModal(elements.ratingsSelectModal);
            });

            elements.okRatingsBtn.addEventListener('click', handleSaveRatings);
            elements.cancelRatingsBtn.addEventListener('click', () => hideModal(elements.ratingsSelectModal));

            elements.unratedContentToggle.addEventListener('change', () => {
                parentalControls.allowUnrated = elements.unratedContentToggle.checked;
                saveParentalControls();
            });

            elements.resetPinBtn.addEventListener('click', () => {
                pinEntryCallback = () => {
                    parentalControls.pin = null;
                    currentPinInput = '';
                    isSettingPin = false;
                    tempPin = '';
                    saveParentalControls();
                    updatePinDisplay(elements.pinDisplay, '');
                    elements.pinStatusText.textContent = 'PIN has been reset. Enter a new PIN.';
                    hideModal(elements.pinEntryModal);
                    showModal(elements.parentalControlsModal);
                };
                elements.pinEntryTitle.textContent = "Enter PIN to Reset";
                showModal(elements.pinEntryModal);
            });

            elements.okPinEntryBtn.addEventListener('click', verifyPin);
            elements.cancelPinEntryBtn.addEventListener('click', () => {
                hideModal(elements.pinEntryModal);
                resetPinEntry();
            });
            elements.closePinEntryModal.addEventListener('click', () => {
                hideModal(elements.pinEntryModal);
                resetPinEntry();
            });
        }

        function isContentAllowed(item) {
            if (!parentalControls.pin) {
                return true;
            }

            const rating = item.parentalRating;

            if (!rating || rating.trim() === '') {
                return parentalControls.allowUnrated;
            }

            return parentalControls.allowedRatings.includes(rating);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.shaka) {
                shaka.polyfill.installAll();
                console.log('✅ Shaka Player polyfills installed');
            }

            init();
        });

        function findContentById(id) {
            if (!cachedContent || cachedContent.length === 0) {
                console.warn("findContentById: cachedContent is empty or not yet populated.");
                if (cineData && cineData.Categories) {
                    for (const category of cineData.Categories) {
                        const foundEntry = category.Entries.find(entry => entry.Title === id);
                        if (foundEntry) {
                            const type = category.MainCategory.toLowerCase().includes('movie') ? 'movie' :
                                         category.MainCategory.toLowerCase().includes('series') ? 'series' : 'live';
                            return { ...foundEntry, type };
                        }
                    }
                }
                return null;
            }
            return cachedContent.find(item => item.Title === id) || null;
        }

        function updateNavigationButtonsState() {
            const isSeriesContext = currentSeries && currentSeries.type === 'series' && currentSeason && currentEpisode;

            if (!elements.prevEpisodeBtn || !elements.nextEpisodeBtn) {
                console.warn("Episode navigation buttons not created.");
                return;
            }

            if (!playerInstance || !playerInstance.elements || !playerInstance.elements.controls) {
                elements.prevEpisodeBtn.style.display = 'none';
                elements.nextEpisodeBtn.style.display = 'none';
                return;
            }

            const controlsContainer = playerInstance.elements.controls;

            if (!isSeriesContext) {
                elements.prevEpisodeBtn.style.display = 'none';
                elements.nextEpisodeBtn.style.display = 'none';
                return;
            }

            const settingsButton = controlsContainer.querySelector('button[data-plyr="settings"]');
            if (settingsButton) {
                if (!controlsContainer.contains(elements.prevEpisodeBtn)) {
                    settingsButton.parentNode.insertBefore(elements.prevEpisodeBtn, settingsButton);
                }
                if (!controlsContainer.contains(elements.nextEpisodeBtn)) {
                    settingsButton.parentNode.insertBefore(elements.nextEpisodeBtn, settingsButton);
                }
            } else {
                if (!controlsContainer.contains(elements.prevEpisodeBtn)) {
                    controlsContainer.appendChild(elements.prevEpisodeBtn);
                }
                if (!controlsContainer.contains(elements.nextEpisodeBtn)) {
                    controlsContainer.appendChild(elements.nextEpisodeBtn);
                }
            }

            elements.prevEpisodeBtn.style.display = '';
            elements.nextEpisodeBtn.style.display = '';

            const currentEpisodeIndex = currentSeason.Episodes.findIndex(ep => ep.Episode === currentEpisode.Episode);
            const currentSeasonIndex = currentSeries.Seasons.findIndex(s => s.Season === currentSeason.Season);

            elements.prevEpisodeBtn.disabled = !(currentEpisodeIndex > 0 || currentSeasonIndex > 0);
            elements.nextEpisodeBtn.disabled = !(currentEpisodeIndex < currentSeason.Episodes.length - 1 || currentSeasonIndex < currentSeries.Seasons.length - 1);
        }

        function handlePrevEpisode() {
            if (!currentSeries || !currentSeason || !currentEpisode) return;

            const currentEpisodeIndex = currentSeason.Episodes.findIndex(ep => ep.Episode === currentEpisode.Episode);
            const currentSeasonIndex = currentSeries.Seasons.findIndex(s => s.Season === currentSeason.Season);

            if (currentEpisodeIndex > 0) {
                playEpisode(currentSeason.Episodes[currentEpisodeIndex - 1], true);
            } else {
                if (currentSeasonIndex > 0) {
                    const prevSeasonData = currentSeries.Seasons[currentSeasonIndex - 1];
                    if (prevSeasonData.Episodes && prevSeasonData.Episodes.length > 0) {
                        currentSeason = prevSeasonData;

                        elements.episodeSelector.innerHTML = '<option value="">Select Episode</option>';
                        currentSeason.Episodes.forEach(ep => {
                            const option = document.createElement('option');
                            option.value = ep.Episode;
                            option.textContent = `Episode ${ep.Episode}: ${ep.Title}`;
                            elements.episodeSelector.appendChild(option);
                        });

                        const targetEpisode = currentSeason.Episodes[currentSeason.Episodes.length - 1];
                        playEpisode(targetEpisode, true);
                    }
                }
            }
        }

        function handleNextEpisode() {
            if (!currentSeries || !currentSeason || !currentEpisode) return;

            const currentEpisodeIndex = currentSeason.Episodes.findIndex(ep => ep.Episode === currentEpisode.Episode);
            const currentSeasonIndex = currentSeries.Seasons.findIndex(s => s.Season === currentSeason.Season);

            if (currentEpisodeIndex < currentSeason.Episodes.length - 1) {
                playEpisode(currentSeason.Episodes[currentEpisodeIndex + 1], true);
            } else {
                if (currentSeasonIndex < currentSeries.Seasons.length - 1) {
                    const nextSeasonData = currentSeries.Seasons[currentSeasonIndex + 1];
                    openSeason(nextSeasonData);
                }
            }
        }

        function displayTemporaryMessage(element, message) {
            const originalText = element.textContent;
            element.textContent = message;
            setTimeout(() => {
                element.textContent = originalText;
            }, 2000);
        }

        async function handleShareVideo() {
            if (!currentContentInfo || !currentContentInfo.Title) {
                console.warn("No content info to share.");
                return;
            }

            let shareTitle = currentContentInfo.Title;
            let shareText = `Watch "${currentContentInfo.Title}" on CineCraze!`;
            const shareUrl = window.location.href;

            if (currentContentInfo.type === 'series' && currentEpisode && currentEpisode.Title) {
                shareTitle = `${currentSeries.Title} - ${currentEpisode.Title}`;
                shareText = `Watch "${currentSeries.Title} - Episode ${currentEpisode.Episode}: ${currentEpisode.Title}" on CineCraze!`;
            } else if (currentContentInfo.type === 'series' && currentSeries && currentSeries.Title) {
                shareTitle = currentSeries.Title;
                shareText = `Check out the series "${currentSeries.Title}" on CineCraze!`;
            }

            const shareData = {
                title: `Check out: ${shareTitle}`,
                text: shareText,
                url: shareUrl
            };

            if (navigator.share) {
                try {
                    await navigator.share(shareData);
                    console.log('Content shared successfully');
                } catch (err) {
                    console.error('Error sharing content:', err);
                    if (err.name !== 'AbortError') {
                        navigator.clipboard.writeText(shareData.url).then(() => {
                            displayTemporaryMessage(elements.shareVideoBtn.querySelector('span'), 'Link Copied!');
                        }).catch(clipErr => {
                            console.error('Fallback clipboard error:', clipErr);
                            alert('Failed to copy link.');
                        });
                    }
                }
            } else {
                try {
                    await navigator.clipboard.writeText(shareData.url);
                    displayTemporaryMessage(elements.shareVideoBtn.querySelector('span'), 'Link Copied!');
                } catch (err) {
                    console.error('Could not copy text: ', err);
                    alert('Failed to copy link. Please copy it manually: ' + shareData.url);
                }
            }
        }

        const INTERACTIONS_KEY = 'cineCrazeInteractions';

        function getVideoInteractions(contentId) {
            const allInteractions = JSON.parse(localStorage.getItem(INTERACTIONS_KEY)) || {};
            const likes = parseInt(allInteractions[contentId]?.likes, 10);
            const dislikes = parseInt(allInteractions[contentId]?.dislikes, 10);
            return {
                likes: isNaN(likes) ? 0 : likes,
                dislikes: isNaN(dislikes) ? 0 : dislikes,
                userAction: allInteractions[contentId]?.userAction || null
            };
        }

        function saveVideoInteractions(contentId, interactionData) {
            const allInteractions = JSON.parse(localStorage.getItem(INTERACTIONS_KEY)) || {};
            allInteractions[contentId] = interactionData;
            localStorage.setItem(INTERACTIONS_KEY, JSON.stringify(allInteractions));
        }

        function formatCount(count) {
            if (count >= 1000000) return (count / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
            if (count >= 1000) return (count / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
            return count.toString();
        }

        function updateLikeDislikeUI(contentId) {
            const likeCheckbox = document.getElementById('like-checkbox');
            const dislikeCheckbox = document.getElementById('dislike-checkbox');
            const likeCountSpan = document.getElementById('like-count-span');
            const dislikeCountSpan = document.getElementById('dislike-count-span');

            if (!likeCheckbox || !dislikeCheckbox || !likeCountSpan || !dislikeCountSpan) return;

            const interactions = getVideoInteractions(contentId);
            likeCountSpan.textContent = formatCount(interactions.likes);
            dislikeCountSpan.textContent = formatCount(interactions.dislikes);

            likeCheckbox.checked = interactions.userAction === 'liked';
            dislikeCheckbox.checked = interactions.userAction === 'disliked';
        }

        function handleLike() {
            if (!currentContentInfo || !currentContentInfo.Title) return;
            const contentId = currentContentInfo.Title;
            let interactions = getVideoInteractions(contentId);

            if (interactions.userAction === 'liked') {
                interactions.likes--;
                interactions.userAction = null;
            } else {
                if (interactions.userAction === 'disliked') interactions.dislikes--;
                interactions.likes++;
                interactions.userAction = 'liked';
            }
            saveVideoInteractions(contentId, interactions);
            updateLikeDislikeUI(contentId);
        }

        function handleDislike() {
            if (!currentContentInfo || !currentContentInfo.Title) return;
            const contentId = currentContentInfo.Title;
            let interactions = getVideoInteractions(contentId);

            if (interactions.userAction === 'disliked') {
                interactions.dislikes--;
                interactions.userAction = null;
            } else {
                if (interactions.userAction === 'liked') interactions.likes--;
                interactions.dislikes++;
                interactions.userAction = 'disliked';
            }
            saveVideoInteractions(contentId, interactions);
            updateLikeDislikeUI(contentId);
        }

        async function toggleWatchLater(content, buttonElement) {
            if (!content || !content.Title) return;
            const contentId = content.Title;
            const db = await watchLaterDbUtil.open();
            const existing = await watchLaterDbUtil.get(db, contentId);

            if (existing) {
                await watchLaterDbUtil.delete(db, contentId);
                alert(`"${content.Title}" removed from Watch Later.`);
                watchLaterItemsSet.delete(contentId);
                if (buttonElement) buttonElement.classList.remove('active');
            } else {
                const itemToStore = { ...content, id: contentId };
                await watchLaterDbUtil.set(db, itemToStore);
                alert(`"${content.Title}" added to Watch Later.`);
                watchLaterItemsSet.add(contentId);
                if (buttonElement) buttonElement.classList.add('active');
            }
            db.close();
            updateWatchLaterButton(contentId);
        }

        async function updateWatchLaterButton(contentId) {
            const watchLaterBtn = document.getElementById('watch-later-btn');
            if (!watchLaterBtn) return;

            const db = await watchLaterDbUtil.open();
            const existing = await watchLaterDbUtil.get(db, contentId);
            db.close();

            if (existing) {
                watchLaterBtn.classList.add('active');
            } else {
                watchLaterBtn.classList.remove('active');
            }
        }

        async function deleteFromWatchLater(contentId) {
            if (!contentId) return;
            const db = await watchLaterDbUtil.open();
            await watchLaterDbUtil.delete(db, contentId);
            db.close();
            console.log('Removed from Watch Later:', contentId);
            watchLaterItemsSet.delete(contentId);
            await renderContent('watch-later');
        }

        const VIEW_COUNTS_KEY = 'cineCrazeViewCounts';

        function getViewCounts() {
            return JSON.parse(localStorage.getItem(VIEW_COUNTS_KEY)) || {};
        }

        function incrementViewCount(contentId) {
            if (!contentId) return;
            const viewCounts = getViewCounts();
            viewCounts[contentId] = (viewCounts[contentId] || 0) + 1;
            localStorage.setItem(VIEW_COUNTS_KEY, JSON.stringify(viewCounts));

            const viewsElement = document.getElementById('viewer-views');
            if (viewsElement) {
                viewsElement.innerHTML = `<i class="fas fa-eye"></i> ${formatCount(viewCounts[contentId])} views`;
            }
        }

        function toggleStretch() {
            isStretched = !isStretched;
            const videoElement = playerInstance.elements.container.querySelector('video');
            if (videoElement) {
                if (isStretched) {
                    videoElement.style.objectFit = 'fill';
                    elements.stretchBtn.classList.add('active');
                } else {
                    videoElement.style.objectFit = 'contain';
                    elements.stretchBtn.classList.remove('active');
                }
            }
        }

        function addPipButtonToPlayer() {
            if (!playerInstance || !playerInstance.elements || !playerInstance.elements.controls) {
                return;
            }

            const controlsContainer = playerInstance.elements.controls;
            const settingsButton = controlsContainer.querySelector('button[data-plyr="settings"]');

            const pipButton = document.createElement('button');
            pipButton.type = 'button';
            pipButton.className = 'plyr__controls__item plyr__control';
            pipButton.innerHTML = '<i class="fas fa-external-link-alt"></i>';
            pipButton.setAttribute('aria-label', 'Picture-in-Picture');

            pipButton.addEventListener('click', () => {
                if (typeof Android !== 'undefined' && Android.enterPictureInPicture) {
                    Android.enterPictureInPicture();
                }
            });

            if (settingsButton) {
                settingsButton.parentNode.insertBefore(pipButton, settingsButton);
            } else {
                controlsContainer.appendChild(pipButton);
            }
        }

        playerInstance.on('ready', event => {
            addPipButtonToPlayer();
        });

    </script>
</body>
</html>