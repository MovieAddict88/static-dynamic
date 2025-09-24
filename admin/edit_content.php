<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../config.php';
require_once 'tmdb_handler.php'; // Use the centralized function

$contentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

if (isset($_SESSION['status_message'])) {
    $message = $_SESSION['status_message'];
    unset($_SESSION['status_message']);
}
if ($contentId === 0) die("Invalid content ID.");
$pdo = connect_db();
if (!$pdo) die("Database connection failed.");

// Fetch all configured servers
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_embed_servers'");
$stmt->execute();
$all_configured_servers_json = $stmt->fetchColumn();
$all_configured_servers = $all_configured_servers_json ? json_decode($all_configured_servers_json, true) : [];

// Filter for only enabled servers to be used in UI suggestions
$enabled_servers = array_filter($all_configured_servers, function($server){ return !empty($server['enabled']); });
$enabled_server_urls = array_column($enabled_servers, 'url');

// Helper to parse a URL. It needs all servers (even disabled ones) to correctly parse old, saved URLs.
function parse_server_url($url, $all_servers) {
    if (empty($all_servers) || !is_array($all_servers)) return ['base' => 'custom', 'path' => $url];
    $all_server_urls = array_column($all_servers, 'url');
    foreach ($all_server_urls as $server_base) {
        if (strpos($url, $server_base) === 0) {
            return ['base' => $server_base, 'path' => substr($url, strlen($server_base))];
        }
    }
    return ['base' => 'custom', 'path' => $url];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? 'save';
    try {
        if ($action === 'save') {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE content SET title = ?, description = ?, release_year = ?, poster_url = ? WHERE id = ?");
            $stmt->execute([$_POST['title'], $_POST['description'], $_POST['release_year'], $_POST['poster_url'], $contentId]);
            $process_servers = function($submitted_servers, $content_id = null, $episode_id = null) use ($pdo) {
                if ($episode_id) {
                    $deleteStmt = $pdo->prepare("DELETE FROM servers WHERE episode_id = ?");
                    $deleteStmt->execute([$episode_id]);
                } else {
                    $deleteStmt = $pdo->prepare("DELETE FROM servers WHERE content_id = ?");
                    $deleteStmt->execute([$content_id]);
                }
                foreach ($submitted_servers as $server) {
                    if (empty($server['path'])) continue;
                    $final_url = ($server['base'] === 'custom') ? $server['path'] : $server['base'] . $server['path'];
                    $name = parse_url($final_url, PHP_URL_HOST) ?? 'Custom Server';
                    $insertStmt = $pdo->prepare("INSERT INTO servers (content_id, episode_id, name, url) VALUES (?, ?, ?, ?)");
                    $insertStmt->execute([$content_id, $episode_id, $name, $final_url]);
                }
            };
            if (isset($_POST['servers'])) $process_servers($_POST['servers'], $contentId);
            if (isset($_POST['seasons'])) {
                foreach ($_POST['seasons'] as $season_id => $season_data) {
                     foreach ($season_data['episodes'] as $episode_id => $episode_data) {
                        if (isset($episode_data['servers'])) $process_servers($episode_data['servers'], null, $episode_id);
                     }
                }
            }
            $pdo->commit();
            $message = "Content updated successfully!";

        } elseif ($action === 'apply_servers') {
            $content_info = $pdo->query("SELECT type, tmdb_id FROM content WHERE id = $contentId")->fetch();
            $links_added = 0;

            if ($content_info['type'] === 'movie') {
                $existing_urls = $pdo->query("SELECT url FROM servers WHERE content_id = $contentId")->fetchAll(PDO::FETCH_COLUMN, 0);
                foreach ($enabled_servers as $server) {
                    $expected_url = generate_final_url($server['url'], 'movie', $content_info['tmdb_id']);
                    if (!in_array($expected_url, $existing_urls)) {
                        $name = parse_url($server['url'], PHP_URL_HOST);
                        $pdo->prepare("INSERT INTO servers (content_id, name, url) VALUES (?, ?, ?)")->execute([$contentId, $name, $expected_url]);
                        $links_added++;
                    }
                }
            } elseif ($content_info['type'] === 'series') {
                $seasons = $pdo->query("SELECT id, season_number FROM seasons WHERE content_id = $contentId")->fetchAll();
                foreach($seasons as $season) {
                    $episodes = $pdo->query("SELECT id, episode_number FROM episodes WHERE season_id = {$season['id']}")->fetchAll();
                    foreach($episodes as $episode) {
                        $existing_urls = $pdo->query("SELECT url FROM servers WHERE episode_id = {$episode['id']}")->fetchAll(PDO::FETCH_COLUMN, 0);
                        foreach ($enabled_servers as $server) {
                            $expected_url = generate_final_url($server['url'], 'tv', $content_info['tmdb_id'], $season['season_number'], $episode['episode_number']);
                            if (!in_array($expected_url, $existing_urls)) {
                                $name = parse_url($server['url'], PHP_URL_HOST);
                                $pdo->prepare("INSERT INTO servers (episode_id, name, url) VALUES (?, ?, ?)")->execute([$episode['id'], $name, $expected_url]);
                                $links_added++;
                            }
                        }
                    }
                }
            }
            $_SESSION['status_message'] = "Operation successful. Added {$links_added} new server link(s) from enabled servers.";
            header("Location: edit_content.php?id=$contentId");
            exit;
        }
    } catch (Exception $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        $error = "An error occurred: " . $e->getMessage();
    }
}

// Fetch all content data for display
$stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?"); $stmt->execute([$contentId]);
$content = $stmt->fetch();
if (!$content) die("Content not found.");
if ($content['type'] === 'series') {
    $content['seasons'] = $pdo->query("SELECT * FROM seasons WHERE content_id = $contentId ORDER BY season_number")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($content['seasons'] as &$season) {
        $season['episodes'] = $pdo->query("SELECT * FROM episodes WHERE season_id = {$season['id']} ORDER BY episode_number")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($season['episodes'] as &$episode) {
            $episode['servers'] = $pdo->query("SELECT * FROM servers WHERE episode_id = {$episode['id']}")->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} else {
    $content['servers'] = $pdo->query("SELECT * FROM servers WHERE content_id = $contentId")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content - <?php echo htmlspecialchars($content['title']); ?></title>
    <link rel="stylesheet" href="admin.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <header class="main-header">
        <h1><i class="fas fa-edit"></i> Edit Content</h1>
        <div class="user-info"><a href="index.php#data-management">Back to Dashboard</a></div>
    </header>

    <?php if ($message): ?><div class="status success" id="status-message"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="status error" id="status-message"><?php echo $error; ?></div><?php endif; ?>

    <div class="card">
        <form action="edit_content.php?id=<?php echo $contentId; ?>&action=save" method="POST">
            <div class="form-group"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($content['title']); ?>"></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="5"><?php echo htmlspecialchars($content['description']); ?></textarea></div>
            <div class="grid grid-2">
                <div class="form-group"><label>Year</label><input type="number" name="release_year" value="<?php echo htmlspecialchars($content['release_year']); ?>"></div>
                <div class="form-group"><label>Poster URL</label><input type="url" name="poster_url" value="<?php echo htmlspecialchars($content['poster_url']); ?>"></div>
            </div>

            <?php
            function render_server_inputs($servers, $name_prefix, $all_servers, $enabled_server_urls, $contentId) {
                echo "<div class='servers-header'><h3><i class='fas fa-link'></i> Servers</h3><form action='edit_content.php?id={$contentId}&action=apply_servers' method='POST' style='display: inline;'><button type='submit' class='btn btn-secondary btn-small'>Apply Configured Servers</button></form></div>";
                echo "<div class='servers-container' id='servers-{$name_prefix}'>";
                if (empty($servers)) echo "<p>No servers attached.</p>";
                foreach ($servers as $server) {
                    $parsed = parse_server_url($server['url'], $all_servers);
                    $id = $server['id'];
                    echo "<div class='server-item' id='server-item-{$id}'><select name='{$name_prefix}[{$id}][base]'>";
                    foreach ($enabled_server_urls as $base_url) {
                        $selected = ($parsed['base'] === $base_url) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($base_url) . "' {$selected}>" . htmlspecialchars(parse_url($base_url, PHP_URL_HOST)) . "</option>";
                    }
                    $custom_selected = ($parsed['base'] === 'custom' || !in_array($parsed['base'], $enabled_server_urls)) ? 'selected' : '';
                    echo "<option value='custom' {$custom_selected}>Custom URL</option>";
                    echo "</select><input type='text' name='{$name_prefix}[{$id}][path]' value='" . htmlspecialchars($parsed['path']) . "' placeholder='Video ID/Path or Full URL'><button type='button' class='btn btn-danger btn-small' onclick='document.getElementById(\"server-item-{$id}\").remove()'>Remove</button></div>";
                }
                echo "</div>";
                echo "<button type='button' class='btn btn-secondary btn-small' onclick='addServer(\"servers-{$name_prefix}\", \"{$name_prefix}\")'>+ Add Server</button>";
            }
            ?>

            <?php if ($content['type'] !== 'series'): ?>
                <?php render_server_inputs($content['servers'], 'servers', $all_configured_servers, $enabled_server_urls, $contentId); ?>
            <?php else: ?>
            <div class="form-group">
                 <div class='servers-header'><h3><i class="fas fa-list-ul"></i> Seasons & Episodes</h3><form action='edit_content.php?id=<?php echo $contentId; ?>&action=apply_servers' method='POST' style='display: inline;'><button type='submit' class='btn btn-secondary btn-small'>Apply Configured Servers to All Episodes</button></form></div>
                <?php foreach ($content['seasons'] as $season): ?>
                <div class="season-group"><h4>Season <?php echo $season['season_number']; ?></h4>
                    <?php foreach ($season['episodes'] as $episode): ?>
                    <div class="episode-group"><h5>Episode <?php echo $episode['episode_number']; ?>: <?php echo htmlspecialchars($episode['title']); ?></h5>
                        <?php
                        $name_prefix = "seasons[{$season['id']}][episodes][{$episode['id']}][servers]";
                        echo "<div class='servers-container' id='servers-{$name_prefix}'>";
                        if (empty($episode['servers'])) echo "<p>No servers attached.</p>";
                        foreach ($episode['servers'] as $server) {
                            $parsed = parse_server_url($server['url'], $all_configured_servers);
                            $id = $server['id'];
                            echo "<div class='server-item' id='server-item-{$id}'><select name='{$name_prefix}[{$id}][base]'>";
                            foreach ($enabled_server_urls as $base_url) {
                                $selected = ($parsed['base'] === $base_url) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($base_url) . "' {$selected}>" . htmlspecialchars(parse_url($base_url, PHP_URL_HOST)) . "</option>";
                            }
                            $custom_selected = ($parsed['base'] === 'custom' || !in_array($parsed['base'], $enabled_server_urls)) ? 'selected' : '';
                            echo "<option value='custom' {$custom_selected}>Custom URL</option>";
                            echo "</select><input type='text' name='{$name_prefix}[{$id}][path]' value='" . htmlspecialchars($parsed['path']) . "' placeholder='Video ID/Path or Full URL'><button type='button' class='btn btn-danger btn-small' onclick='document.getElementById(\"server-item-{$id}\").remove()'>Remove</button></div>";
                        }
                        echo "</div>";
                        echo "<button type='button' class='btn btn-secondary btn-small' onclick='addServer(\"servers-{$name_prefix}\", \"{$name_prefix}\")'>+ Add Server</button>";
                        ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Save All Changes</button>
        </form>
    </div>
</div>
<script>
const enabledServers = <?php echo json_encode($enabled_server_urls); ?>;
function addServer(containerId, namePrefix) {
    const container = document.getElementById(containerId);
    const newItem = document.createElement('div');
    newItem.className = 'server-item';
    const newIndex = 'new_' + Date.now();
    let optionsHtml = enabledServers.map(url => `<option value="${escapeHTML(url)}">${escapeHTML(new URL(url).hostname)}</option>`).join('');
    optionsHtml += `<option value="custom">Custom URL</option>`;
    newItem.innerHTML = `<select name="${namePrefix}[${newIndex}][base]">${optionsHtml}</select><input type="text" name="${namePrefix}[${newIndex}][path]" placeholder="Video ID/Path or Full URL"><button type="button" class="btn btn-danger btn-small" onclick="this.parentElement.remove()">Remove</button>`;
    container.appendChild(newItem);
}
function escapeHTML(str) { return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
const statusMessage = document.getElementById('status-message');
if (statusMessage) { setTimeout(() => { statusMessage.style.display = 'none'; }, 5000); }
</script>
</body>
</html>
