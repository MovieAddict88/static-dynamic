<?php
require_once 'auth.php'; // Protect this page
require_once __DIR__ . '/../includes/db.php'; // Include DB connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#e50914">
    <title>Admin Dashboard - CineCraze</title>
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b8070f;
            --secondary: #221f1f;
            --background: #0a0a0a;
            --surface: #1a1a1a;
            --surface-light: #2d2d2d;
            --surface-hover: #333333;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --text-muted: #808080;
            --success: #46d369;
            --warning: #ffa500;
            --danger: #f40612;
            --accent: #00d4ff;
            --accent-dark: #0099cc;
            --bottom-bar-height: 80px;
            --border-radius: 16px;
            --border-radius-sm: 12px;
            --shadow: 0 8px 32px rgba(0,0,0,0.4);
            --shadow-hover: 0 16px 48px rgba(0,0,0,0.6);
            --shadow-primary: 0 8px 32px rgba(229, 9, 20, 0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(ellipse at center, var(--surface) 0%, var(--background) 70%, var(--secondary) 100%);
            color: var(--text);
            min-height: 100vh;
            padding: max(20px, env(safe-area-inset-top)) max(20px, env(safe-area-inset-right)) calc(var(--bottom-bar-height) + max(20px, env(safe-area-inset-bottom))) max(20px, env(safe-area-inset-left));
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 30px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-primary);
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            pointer-events: none;
        }

        h1 {
            font-size: clamp(1.8rem, 4vw, 3rem);
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .subtitle {
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            opacity: 0.9;
            font-weight: 300;
        }

        /* Bottom Navigation Bar */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-light) 100%);
            border-top: 2px solid var(--primary);
            box-shadow: 0 -8px 32px rgba(0,0,0,0.4);
            backdrop-filter: blur(20px);
            z-index: 1000;
            height: var(--bottom-bar-height);
            -webkit-backdrop-filter: blur(20px); /* Safari support */
        }

        .nav-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 max(10px, env(safe-area-inset-left)) 0 max(10px, env(safe-area-inset-right));
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.3s ease;
            min-width: 60px;
            text-decoration: none;
            color: var(--text-secondary);
        }

        .nav-item:hover {
            background: rgba(229, 9, 20, 0.1);
            color: var(--text);
            transform: translateY(-2px);
        }

        .nav-item:active {
            transform: translateY(0);
            transition: transform 0.1s ease;
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4);
        }

        .nav-item:focus {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        .nav-item:focus:not(:focus-visible) {
            outline: none;
        }

        .nav-icon {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            margin-bottom: 4px;
        }

        .nav-label {
            font-size: clamp(0.7rem, 2vw, 0.8rem);
            font-weight: 600;
            text-align: center;
            line-height: 1;
        }

        .card {
            background: var(--surface);
            border-radius: var(--border-radius);
            padding: clamp(25px, 4vw, 35px);
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-light);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--surface-hover);
        }

        .card:hover::before {
            transform: scaleX(1);
        }

        .card h2 {
            color: var(--primary);
            margin-bottom: 25px;
            font-size: clamp(1.3rem, 3vw, 1.8rem);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card h2::before {
            content: "🎬";
            font-size: 1.5em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        input, select, textarea {
            width: 100%;
            padding: clamp(14px, 2.5vw, 18px);
            border: 2px solid var(--surface-light);
            border-radius: var(--border-radius-sm);
            background: var(--background);
            color: var(--text);
            font-size: clamp(14px, 2.5vw, 16px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(229, 9, 20, 0.15);
            background: var(--surface);
            transform: translateY(-1px);
        }

        input:hover, select:hover, textarea:hover {
            border-color: var(--surface-hover);
            background: var(--surface);
        }

        .btn {
            padding: clamp(14px, 2.5vw, 18px) clamp(24px, 4vw, 32px);
            border: none;
            border-radius: var(--border-radius-sm);
            font-size: clamp(14px, 2.5vw, 16px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin: 6px;
            text-align: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            font-family: inherit;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4);
        }

        .btn-secondary {
            background: var(--surface-light);
            color: var(--text);
            border: 2px solid var(--surface-light);
        }

        .btn-secondary:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--background);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-accent {
            background: var(--accent);
            color: var(--background);
            font-weight: bold;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(min(400px, 100%), 1fr));
        }

        /* Tab content styling */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(200px, 100%), 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .preview-item {
            background: var(--surface-light);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .preview-item:hover {
            transform: scale(1.05);
        }

        .preview-item img {
            width: 100%;
            height: clamp(200px, 40vw, 300px);
            object-fit: cover;
        }

        .preview-item .info {
            padding: 15px;
        }

        .preview-item .title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }

        .preview-item .meta {
            font-size: clamp(0.8rem, 1.8vw, 0.9rem);
            color: var(--text-secondary);
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: var(--surface-light);
            border-radius: 3px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-top: 5px;
        }

        .status {
            padding: 15px 20px;
            border-radius: var(--border-radius-sm);
            margin: 15px 0;
            font-weight: 600;
            font-size: clamp(0.9rem, 2vw, 1rem);
            border-left: 4px solid;
            position: relative;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .status:hover {
            transform: translateX(5px);
        }

        .status.success {
            background: rgba(70, 211, 105, 0.1);
            color: var(--success);
            border-left-color: var(--success);
        }
        .status.warning {
            background: rgba(255, 165, 0, 0.1);
            color: var(--warning);
            border-left-color: var(--warning);
        }
        .status.error {
            background: rgba(244, 6, 18, 0.1);
            color: var(--danger);
            border-left-color: var(--danger);
        }
        .status.info {
            background: rgba(0, 212, 255, 0.1);
            color: var(--accent);
            border-left-color: var(--accent);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--surface-light);
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }

        .modal-content {
            background-color: var(--surface);
            margin: 5% auto;
            padding: clamp(20px, 4vw, 30px);
            border-radius: 15px;
            width: min(90%, 800px);
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: var(--text-secondary);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--primary);
        }

        .server-list {
            margin-top: 15px;
        }

        .server-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .server-item input {
            flex: 1;
            min-width: 200px;
        }

        .server-item .paste-btn {
            padding: 8px 12px;
            background: var(--accent);
            color: var(--background);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .server-item .paste-btn:hover {
            background: var(--primary);
            transform: translateY(-1px);
        }

        .btn-small {
            padding: 8px 15px;
            font-size: clamp(12px, 2vw, 14px);
        }

        .season-group {
            border: 1px solid var(--surface-light);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: var(--surface);
        }

        .episode-group {
            margin: 10px 0;
            padding: 10px;
            background: var(--background);
            border-radius: 5px;
            border-left: 3px solid var(--primary);
        }

        .episode-group h5 {
            margin-bottom: 10px;
            color: var(--primary);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid var(--surface-light);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .auto-embed-config {
            background: var(--surface-light);
            border-radius: var(--border-radius-sm);
            padding: 25px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 15px;
            border: 1px solid var(--surface-hover);
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.1);
        }

        .embed-option {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
            padding: 15px;
            background: var(--background);
            border-radius: var(--border-radius-sm);
            border: 2px solid var(--surface-light);
            flex-wrap: wrap;
            transition: all 0.3s ease;
            position: relative;
        }

        .embed-option:hover {
            border-color: var(--primary);
            background: var(--surface);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }

        .embed-option:last-child {
            margin-bottom: 0;
        }

        .embed-option input[type="checkbox"] {
            width: auto;
            margin: 0;
            transform: scale(1.2);
            accent-color: var(--primary);
        }

        .embed-option label {
            flex: 1;
            margin: 0;
            font-weight: 600;
            color: var(--text);
            min-width: 160px;
            font-size: 0.95rem;
        }

        .embed-option select {
            width: auto;
            min-width: 110px;
            padding: 10px 14px;
            background: var(--surface);
            border: 1px solid var(--surface-hover);
            color: var(--text);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .auto-embed-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .auto-embed-actions .btn {
            flex: 1;
            min-width: 180px;
            position: relative;
        }

        .auto-embed-actions .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .auto-embed-actions .btn:hover::after {
            transform: translateX(100%);
        }

        .tmdb-update-container {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .tmdb-input-wrapper {
            flex: 1;
            min-width: 200px;
        }

        .tmdb-update-btn {
            white-space: nowrap;
            flex-shrink: 0;
        }

        #auto-embed-content-select {
            max-height: 200px;
            overflow-y: auto;
        }

        #selected-content-info {
            margin-top: 10px;
            padding: 15px;
            border-radius: 8px;
            background: var(--surface-light);
            border-left: 4px solid var(--accent);
        }

        .checkbox-content-container {
            background: var(--surface-light);
            border-radius: 10px;
            padding: 15px;
            border: 1px solid var(--surface-light);
        }

        .checkbox-header {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .selection-counter {
            font-weight: 600;
            color: var(--accent);
            margin-left: auto;
        }

        .api-status {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 8px;
            display: block;
        }

        .api-status span {
            color: var(--success);
            font-weight: bold;
        }

        .content-checkbox-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--surface-light);
            border-radius: 8px;
            background: var(--background);
        }

        .content-checkbox-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid var(--surface-light);
            transition: background 0.2s ease;
            position: relative;
        }

        .content-checkbox-item:last-child {
            border-bottom: none;
        }

        .content-checkbox-item:hover {
            background: var(--surface);
        }

        .content-checkbox-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .content-checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0 12px 0 0;
            cursor: pointer;
        }

        .content-checkbox-item.disabled input[type="checkbox"] {
            cursor: not-allowed;
        }

        .content-checkbox-label {
            flex: 1;
            font-weight: 500;
            color: var(--text);
            cursor: pointer;
            line-height: 1.4;
        }

        .content-checkbox-item.disabled .content-checkbox-label {
            cursor: not-allowed;
        }

        .tmdb-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            margin-left: auto;
        }

        .tmdb-status.searching {
            color: var(--warning);
        }

        .tmdb-status.found {
            color: var(--success);
        }

        .tmdb-status.not-found {
            color: var(--danger);
        }

        .tmdb-status .loading-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid var(--surface-light);
            border-radius: 50%;
            border-top-color: var(--warning);
            animation: spin 1s ease-in-out infinite;
        }

        .manual-tmdb-input {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }

        .manual-tmdb-input input {
            width: 80px;
            padding: 4px 8px;
            font-size: 0.8rem;
            border: 1px solid var(--surface-light);
            border-radius: 4px;
            background: var(--background);
            color: var(--text);
        }

        .manual-tmdb-input input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(0, 212, 255, 0.2);
        }

        .manual-tmdb-input .btn-verify {
            padding: 4px 8px;
            font-size: 0.75rem;
            min-width: auto;
            white-space: nowrap;
        }

        .tmdb-status.not-found {
            flex-direction: column;
            align-items: flex-end;
        }

        .tmdb-status.manual-entry {
            color: var(--accent);
        }

        .bulk-update-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #bulk-update-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 10px;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            body {
                padding: max(15px, env(safe-area-inset-top)) max(15px, env(safe-area-inset-right)) calc(var(--bottom-bar-height) + max(15px, env(safe-area-inset-bottom))) max(15px, env(safe-area-inset-left));
            }

            .container {
                padding: 0;
            }

            header {
                padding: 20px;
                margin-bottom: 30px;
            }

            .card {
                padding: 20px;
                margin-bottom: 20px;
            }

            .grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .preview-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }

            .server-item {
                flex-direction: column;
                align-items: stretch;
            }

            .server-item input {
                min-width: auto;
                margin-bottom: 5px;
            }

            .auto-embed-actions {
                flex-direction: column;
            }

            .auto-embed-actions .btn {
                min-width: auto;
            }
        }

        /* Enhanced Search Results Styling */
        .results-info {
            background: var(--surface-light);
            border-radius: var(--border-radius-sm);
            padding: 15px 20px;
            margin: 20px 0;
            border-left: 4px solid var(--primary);
            font-weight: 600;
            color: var(--text);
            text-align: center;
        }

        .results-info p {
            margin: 0;
            font-size: 1.1rem;
        }

        .results-info strong {
            color: var(--primary);
            font-size: 1.3rem;
        }

        /* Enhanced Form Group Styling */
        .form-group {
            margin-bottom: 30px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: clamp(0.95rem, 2vw, 1.1rem);
            position: relative;
        }

        .form-group label::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 30px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 1px;
        }

        /* Enhanced Grid Layouts */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(min(450px, 100%), 1fr));
            gap: 25px;
        }

        /* Enhanced Preview Grid */
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .preview-item {
            background: var(--surface-light);
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--surface-hover);
            position: relative;
        }

        .preview-item:hover {
            transform: scale(1.05) translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .preview-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .preview-item:hover::before {
            transform: scaleX(1);
        }

        /* Enhanced Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-light) 100%);
            border-top: 3px solid var(--primary);
            box-shadow: 0 -8px 32px rgba(0,0,0,0.4);
            backdrop-filter: blur(20px);
            z-index: 1000;
            height: var(--bottom-bar-height);
            -webkit-backdrop-filter: blur(20px);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            cursor: pointer;
            border-radius: var(--border-radius-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 70px;
            text-decoration: none;
            color: var(--text-secondary);
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .nav-item:hover::before {
            opacity: 0.1;
        }

        .nav-item.active::before {
            opacity: 1;
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(229, 9, 20, 0.4);
            transform: translateY(-3px);
        }

        /* Smooth scrolling and animations */
        html {
            scroll-behavior: smooth;
        }

        /* Enhanced focus states */
        *:focus {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        *:focus:not(:focus-visible) {
            outline: none;
        }

        /* Loading animation improvements */
        .loading {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid var(--surface-light);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Enhanced progress bar */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--surface-light);
            border-radius: 4px;
            overflow: hidden;
            margin: 25px 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            width: 0%;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
            }

            .embed-option {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .embed-option label {
                min-width: auto;
            }

            .tmdb-update-container {
                flex-direction: column;
                align-items: stretch;
            }

            .tmdb-input-wrapper {
                min-width: auto;
                margin-bottom: 10px;
            }

            .tmdb-update-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            :root {
                --bottom-bar-height: 70px;
            }

            body {
                padding: max(10px, env(safe-area-inset-top)) max(10px, env(safe-area-inset-right)) calc(var(--bottom-bar-height) + max(10px, env(safe-area-inset-bottom))) max(10px, env(safe-area-inset-left));
            }

            .nav-container {
                padding: 0 5px;
            }

            .nav-item {
                min-width: 50px;
                padding: 6px 8px;
            }

            .nav-label {
                font-size: 0.65rem;
            }

            .nav-icon {
                font-size: 1.1rem;
            }

            header {
                padding: 15px;
                margin-bottom: 20px;
            }

            .card {
                padding: 15px;
                margin-bottom: 15px;
            }

            .preview-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 10px;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
                padding: 20px;
            }
        }

        /* Landscape orientation for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            :root {
                --bottom-bar-height: 60px;
            }

            body {
                padding: max(10px, env(safe-area-inset-top)) max(10px, env(safe-area-inset-right)) calc(var(--bottom-bar-height) + max(10px, env(safe-area-inset-bottom))) max(10px, env(safe-area-inset-left));
            }

            .nav-item {
                padding: 4px 8px;
            }

            .nav-icon {
                font-size: 1rem;
                margin-bottom: 2px;
            }

            .nav-label {
                font-size: 0.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Panel</h1>
            <p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
        </header>

        <main>
            <div id="tmdb-generator" class="tab-content">
                <div class="grid grid-2">
                    <div class="card">
                        <h2>🎬 Movie Generator</h2>
                        <div class="form-group">
                            <label>TMDB Movie ID</label>
                            <input type="number" id="movie-tmdb-id" placeholder="e.g., 550 (Fight Club)">
                        </div>
                        <button class="btn btn-primary" onclick="generateFromTMDB('movie')">
                            <span class="loading" id="movie-loading" style="display: none;"></span>
                            Generate Movie
                        </button>
                    </div>

                    <div class="card">
                        <h2>📺 TV Series Generator</h2>
                        <div class="form-group">
                            <label>TMDB TV Series ID</label>
                            <input type="number" id="series-tmdb-id" placeholder="e.g., 1399 (Game of Thrones)">
                        </div>
                        <div class="form-group">
                            <label>Seasons to Include (optional)</label>
                            <input type="text" id="series-seasons" placeholder="e.g., 1,2,3 or leave empty for all">
                        </div>
                        <button class="btn btn-primary" onclick="generateFromTMDB('series')">
                            <span class="loading" id="series-loading" style="display: none;"></span>
                            Generate Series
                        </button>
                    </div>
                </div>
            </div>
            <div id="data-management" class="tab-content">
                <div class="grid">
                    <div class="card">
                        <h2>📂 Import/Export</h2>
                        <form id="import-form" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Import JSON File</label>
                                <input type="file" id="import-file" name="import_file" accept=".json">
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="loading" id="import-loading" style="display: none;"></span>
                                        🚀 Import Now
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="form-group">
                            <a href="data_manager.php?action=export_json" class="btn btn-primary">Export Current Data</a>
                        </div>
                    </div>

                    <div class="card">
                        <h2>🗑️ Data Management</h2>
                        <div class="form-group">
                            <button class="btn btn-danger" id="clear-data-btn">Clear All Data</button>
                        </div>
                        <div class="form-group">
                            <label>Current Data Count</label>
                            <div id="data-stats">
                                <div>Movies: <span id="movie-count">0</span></div>
                                <div>Series: <span id="series-count">0</span></div>
                                <div>Channels: <span id="channel-count">0</span></div>
                                <div>Total Items: <span id="total-count">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <div class="nav-item active" onclick="switchTab('tmdb-generator')" role="button" tabindex="0" aria-label="TMDB Generator">
                <div class="nav-icon">🎭</div>
                <div class="nav-label">TMDB</div>
            </div>
            <div class="nav-item" onclick="switchTab('data-management')" role="button" tabindex="0" aria-label="Data Management">
                <div class="nav-icon">🗂️</div>
                <div class="nav-label">Data</div>
            </div>
            <a href="change_password.php" class="nav-item" role="button" aria-label="Change Password">
                <div class="nav-icon">🔑</div>
                <div class="nav-label">Password</div>
            </a>
            <a href="logout.php" class="nav-item" role="button" aria-label="Logout">
                <div class="nav-icon">🚪</div>
                <div class="nav-label">Logout</div>
            </a>
        </div>
    </nav>

    <script>
        function showStatus(type, message) {
            let statusEl = document.getElementById('global-status');
            if (!statusEl) {
                statusEl = document.createElement('div');
                statusEl.id = 'global-status';
                statusEl.style.position = 'fixed';
                statusEl.style.top = '20px';
                statusEl.style.right = '20px';
                statusEl.style.zIndex = '9999';
                statusEl.style.maxWidth = '400px';
                document.body.appendChild(statusEl);
            }
            statusEl.innerHTML = `<div class="status ${type}">${message}</div>`;
            setTimeout(() => {
                if (statusEl.parentNode) {
                    statusEl.parentNode.removeChild(statusEl);
                }
            }, 5000);
        }

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            document.querySelector(`.nav-item[onclick*="'${tabName}'"]`).classList.add('active');
        }

        async function updateDataStats() {
            try {
                const response = await fetch('data_manager.php?action=get_stats');
                const result = await response.json();
                if (result.status === 'success') {
                    document.getElementById('movie-count').textContent = result.data.movies;
                    document.getElementById('series-count').textContent = result.data.series;
                    document.getElementById('channel-count').textContent = result.data.live;
                    document.getElementById('total-count').textContent = result.data.total;
                }
            } catch (error) {
                console.error('Error fetching stats:', error);
            }
        }

        async function generateFromTMDB(type) {
            const tmdbId = document.getElementById(`${type}-tmdb-id`).value;
            const seasons = document.getElementById('series-seasons') ? document.getElementById('series-seasons').value : '';
            const loadingSpinner = document.getElementById(`${type}-loading`);

            if (!tmdbId) {
                showStatus('warning', 'Please enter a TMDB ID.');
                return;
            }

            loadingSpinner.style.display = 'inline-block';

            const formData = new FormData();
            formData.append('action', 'generate');
            formData.append('type', type);
            formData.append('tmdb_id', tmdbId);
            if (type === 'series') {
                formData.append('seasons', seasons);
            }

            try {
                const response = await fetch('tmdb_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showStatus('success', result.message);
                    updateDataStats();
                } else {
                    showStatus('error', result.message);
                }
            } catch (error) {
                showStatus('error', 'An unexpected error occurred during generation.');
            } finally {
                loadingSpinner.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            switchTab('tmdb-generator');
            updateDataStats();

            const importForm = document.getElementById('import-form');
            importForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(importForm);
                const loadingSpinner = document.getElementById('import-loading');
                loadingSpinner.style.display = 'inline-block';

                try {
                    const response = await fetch('data_manager.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showStatus('success', result.message);
                        updateDataStats();
                    } else {
                        showStatus('error', result.message);
                    }
                } catch (error) {
                    showStatus('error', 'An unexpected error occurred during import.');
                } finally {
                    loadingSpinner.style.display = 'none';
                }
            });

            const clearDataBtn = document.getElementById('clear-data-btn');
            clearDataBtn.addEventListener('click', async () => {
                if (confirm('Are you sure you want to clear all content data? This cannot be undone.')) {
                    try {
                        const response = await fetch('data_manager.php?action=clear_data', { method: 'POST', body: new FormData() });
                        const result = await response.json();

                        if (result.status === 'success') {
                            showStatus('success', result.message);
                            updateDataStats();
                        } else {
                            showStatus('error', result.message);
                        }
                    } catch (error) {
                         showStatus('error', 'An unexpected error occurred.');
                    }
                }
            });
        });
    </script>
</body>
</html>