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
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_embed_servers'");
$stmt->execute();
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
            <p>Add and manage the base URLs for your video servers. Enable or disable servers that will be used to automatically generate embed links for new content.</p>

            <form id="add-server-form" class="form-inline">
                <div class="form-group">
                    <label for="server-url">New Server URL</label>
                    <input type="text" id="server-url" placeholder="e.g., https://vidsrc.to/embed" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Server</button>
            </form>

            <h3 style="margin-top: 30px;">Configured Servers</h3>
            <ul id="server-list" class="styled-list">
                <?php if (empty($servers)): ?>
                    <li id="no-servers-message">No servers configured yet.</li>
                <?php else: ?>
                    <?php foreach ($servers as $server): ?>
                        <li data-url="<?php echo htmlspecialchars($server['url']); ?>">
                            <label class="switch">
                                <input type="checkbox" <?php echo $server['enabled'] ? 'checked' : ''; ?> onchange="toggleServerStatus(this, '<?php echo htmlspecialchars($server['url']); ?>')">
                                <span class="slider round"></span>
                            </label>
                            <span><?php echo htmlspecialchars($server['url']); ?></span>
                            <button class="btn btn-danger btn-small delete-server-btn">Delete</button>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <script>
        // --- Add Server ---
        document.getElementById('add-server-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const serverUrlInput = document.getElementById('server-url');
            const url = serverUrlInput.value.trim();
            if (!url) return;

            const formData = new FormData();
            formData.append('url', url);

            try {
                const response = await fetch('settings_api.php?action=add_server', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    showStatus('success', result.message);
                    addServerToList({ url: url, enabled: true });
                    serverUrlInput.value = '';
                } else {
                    showStatus('error', result.message || 'An unknown error occurred.');
                }
            } catch (error) {
                showStatus('error', 'Request failed: ' + error.toString());
            }
        });

        // --- Delete Server ---
        document.getElementById('server-list').addEventListener('click', async function(e) {
            if (e.target.classList.contains('delete-server-btn')) {
                const listItem = e.target.closest('li');
                const url = listItem.dataset.url;
                if (!confirm(`Are you sure you want to delete the server: ${url}?`)) return;

                const formData = new FormData();
                formData.append('url', url);

                try {
                    const response = await fetch('settings_api.php?action=delete_server', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.success) {
                        showStatus('success', result.message);
                        listItem.remove();
                        if (document.getElementById('server-list').children.length === 0) {
                             document.getElementById('server-list').innerHTML = '<li id="no-servers-message">No servers configured yet.</li>';
                        }
                    } else {
                        showStatus('error', result.message || 'An unknown error occurred.');
                    }
                } catch (error) {
                    showStatus('error', 'Request failed: ' + error.toString());
                }
            }
        });

        // --- Toggle Server Status ---
        async function toggleServerStatus(checkbox, url) {
            const isEnabled = checkbox.checked;
            const formData = new FormData();
            formData.append('url', url);
            formData.append('enabled', isEnabled ? '1' : '0');

            try {
                const response = await fetch('settings_api.php?action=toggle_server', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    showStatus('success', result.message);
                } else {
                    showStatus('error', result.message || 'An unknown error occurred.');
                    checkbox.checked = !isEnabled; // Revert on failure
                }
            } catch (error) {
                showStatus('error', 'Request failed: ' + error.toString());
                checkbox.checked = !isEnabled; // Revert on failure
            }
        }

        function addServerToList(server) {
            const noServersMessage = document.getElementById('no-servers-message');
            if (noServersMessage) noServersMessage.remove();

            const listItem = document.createElement('li');
            listItem.dataset.url = server.url;
            listItem.innerHTML = `
                <label class="switch">
                    <input type="checkbox" ${server.enabled ? 'checked' : ''} onchange="toggleServerStatus(this, '${escapeHTML(server.url)}')">
                    <span class="slider round"></span>
                </label>
                <span>${escapeHTML(server.url)}</span>
                <button class="btn btn-danger btn-small delete-server-btn">Delete</button>
            `;
            document.getElementById('server-list').appendChild(listItem);
        }

        function showStatus(type, message) {
            const statusContainer = document.getElementById('status-container');
            statusContainer.innerHTML = `<div class="status ${type}">${escapeHTML(message)}</div>`;
            setTimeout(() => { statusContainer.innerHTML = ''; }, 4000);
        }

        function escapeHTML(str) {
            const p = document.createElement('p');
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
        }
    </script>
</body>
</html>
