<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../config.php';

$pdo = connect_db();
if (!$pdo) {
    die("Database connection failed. Please check your configuration.");
}

// Fetch initial server list
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$stmt->execute(['auto_embed_servers']);
$result = $stmt->fetchColumn();
$servers = $result ? json_decode($result, true) : [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CineCraze Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
             <h1><a href="index.php" style="text-decoration: none; color: inherit;"><i class="fas fa-cog"></i> CineCraze Admin</a></h1>
            <div class="user-info">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
                <a href="change_password.php" title="Change Password"><i class="fas fa-key"></i></a>
                <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div id="status-container"></div>

        <div class="card">
            <h2><i class="fas fa-server"></i> Auto-Embed Server Management</h2>
            <p>Add and manage the base URLs for your video servers. These will be used to automatically generate embed links for new content.</p>

            <form id="add-server-form" class="form-inline">
                <div class="form-group">
                    <label for="server-url">New Server URL</label>
                    <input type="text" id="server-url" placeholder="e.g., https://stream.example.com/embed?v=" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Server</button>
            </form>

            <h3 style="margin-top: 30px;">Configured Servers</h3>
            <ul id="server-list" class="styled-list">
                <?php if (empty($servers)): ?>
                    <li id="no-servers-message">No servers configured yet.</li>
                <?php else: ?>
                    <?php foreach ($servers as $server): ?>
                        <li data-url="<?php echo htmlspecialchars($server); ?>">
                            <span><?php echo htmlspecialchars($server); ?></span>
                            <button class="btn btn-danger btn-small delete-server-btn">Delete</button>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addForm = document.getElementById('add-server-form');
            const serverUrlInput = document.getElementById('server-url');
            const serverList = document.getElementById('server-list');
            const statusContainer = document.getElementById('status-container');
            const noServersMessage = document.getElementById('no-servers-message');

            // --- Add Server ---
            addForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const url = serverUrlInput.value.trim();
                if (!url) return;

                const formData = new FormData();
                formData.append('url', url);

                try {
                    const response = await fetch('settings_api.php?action=add_server', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        showStatus('success', result.message);
                        addServerToList(url);
                        serverUrlInput.value = '';
                    } else {
                        showStatus('error', result.message || 'An unknown error occurred.');
                    }
                } catch (error) {
                    showStatus('error', 'Request failed: ' + error.toString());
                }
            });

            // --- Delete Server ---
            serverList.addEventListener('click', async function(e) {
                if (e.target.classList.contains('delete-server-btn')) {
                    const listItem = e.target.closest('li');
                    const url = listItem.dataset.url;

                    if (!confirm(`Are you sure you want to delete the server: ${url}?`)) {
                        return;
                    }

                    const formData = new FormData();
                    formData.append('url', url);

                    try {
                        const response = await fetch('settings_api.php?action=delete_server', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();

                        if (result.success) {
                            showStatus('success', result.message);
                            listItem.remove();
                            if (serverList.children.length === 0) {
                                serverList.innerHTML = '<li id="no-servers-message">No servers configured yet.</li>';
                            }
                        } else {
                            showStatus('error', result.message || 'An unknown error occurred.');
                        }
                    } catch (error) {
                        showStatus('error', 'Request failed: ' + error.toString());
                    }
                }
            });

            function addServerToList(url) {
                if (noServersMessage) {
                    noServersMessage.remove();
                }
                const listItem = document.createElement('li');
                listItem.dataset.url = url;
                listItem.innerHTML = `
                    <span>${escapeHTML(url)}</span>
                    <button class="btn btn-danger btn-small delete-server-btn">Delete</button>
                `;
                serverList.appendChild(listItem);
            }

            function showStatus(type, message) {
                statusContainer.innerHTML = `<div class="status ${type}">${escapeHTML(message)}</div>`;
                setTimeout(() => {
                    statusContainer.innerHTML = '';
                }, 5000);
            }

            function escapeHTML(str) {
                const p = document.createElement('p');
                p.appendChild(document.createTextNode(str));
                return p.innerHTML;
            }
        });
    </script>
</body>
</html>
