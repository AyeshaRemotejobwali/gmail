<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
$folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$query = "SELECT e.*, u.name as sender_name FROM emails e JOIN users u ON e.sender_id = u.id WHERE ";
if ($folder == 'inbox') {
    $query .= "e.recipient_id = ? AND e.folder = 'inbox'";
} elseif ($folder == 'sent') {
    $query .= "e.sender_id = ? AND e.folder = 'sent'";
} elseif ($folder == 'drafts') {
    $query .= "e.sender_id = ? AND e.folder = 'drafts'";
}
if ($search) {
    $query .= " AND (e.subject LIKE ? OR e.body LIKE ? OR u.name LIKE ?)";
}
if ($filter == 'unread') {
    $query .= " AND e.is_read = FALSE";
} elseif ($filter == 'starred') {
    $query .= " AND e.is_starred = TRUE";
}
$stmt = $pdo->prepare($query);
$params = ($search) ? [$user_id, "%$search%", "%$search%", "%$search%"] : [$user_id];
$stmt->execute($params);
$emails = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'star') {
        $email_id = $_POST['email_id'];
        $stmt = $pdo->prepare("UPDATE emails SET is_starred = NOT is_starred WHERE id = ?");
        $stmt->execute([$email_id]);
    } elseif ($_POST['action'] == 'trash') {
        $email_id = $_POST['email_id'];
        $stmt = $pdo->prepare("UPDATE emails SET folder = 'trash' WHERE id = ?");
        $stmt->execute([$email_id]);
    }
    echo "<script>window.location.href='dashboard.php?folder=$folder';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f0f0; }
        .container { display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #fff; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar a { display: block; padding: 10px; margin: 5px 0; text-decoration: none; color: #202124; }
        .sidebar a:hover { background: #e8f0fe; color: #1a73e8; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-bar { width: 50%; padding: 10px; border: 1px solid #dfe1e5; border-radius: 24px; }
        .filter { padding: 10px; border-radius: 4px; }
        .email-list { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .email-item { padding: 15px; border-bottom: 1px solid #dfe1e5; display: flex; justify-content: space-between; align-items: center; }
        .email-item:hover { background: #f1f3f4; }
        .email-item a { text-decoration: none; color: #202124; flex: 1; }
        .email-item .star, .email-item .trash { cursor: pointer; margin-left: 10px; }
        .email-item .star.starred { color: #f4b400; }
        .email-item .trash { color: #d93025; }
        button { padding: 10px 20px; background: #1a73e8; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1669c0; }
        @media (max-width: 600px) { .sidebar { width: 200px; } .search-bar { width: 100%; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <button onclick="window.location.href='compose.php'">Compose</button>
            <a href="?folder=inbox">Inbox</a>
            <a href="?folder=sent">Sent</a>
            <a href="?folder=drafts">Drafts</a>
            <a href="index.php?logout=true">Logout</a>
        </div>
        <div class="content">
            <div class="header">
                <input type="text" class="search-bar" placeholder="Search emails" onkeypress="if(event.key === 'Enter') searchEmails(this.value)">
                <select class="filter" onchange="filterEmails(this.value)">
                    <option value="">All</option>
                    <option value="unread">Unread</option>
                    <option value="starred">Starred</option>
                </select>
            </div>
            <div class="email-list">
                <?php foreach ($emails as $email): ?>
                    <div class="email-item">
                        <a href="view_email.php?id=<?php echo $email['id']; ?>">
                            <span><?php echo htmlspecialchars($email['sender_name']); ?></span> - 
                            <span><?php echo htmlspecialchars($email['subject']); ?></span>
                        </a>
                        <span class="star <?php echo $email['is_starred'] ? 'starred' : ''; ?>" onclick="toggleStar(<?php echo $email['id']; ?>)">★</span>
                        <span class="trash" onclick="moveToTrash(<?php echo $email['id']; ?>)">🗑️</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        function searchEmails(query) {
            window.location.href = `dashboard.php?folder=<?php echo $folder; ?>&search=${encodeURIComponent(query)}`;
        }
        function filterEmails(filter) {
            window.location.href = `dashboard.php?folder=<?php echo $folder; ?>&filter=${filter}`;
        }
        function toggleStar(emailId) {
            fetch('dashboard.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=star&email_id=${emailId}`
            }).then(() => location.reload());
        }
        function moveToTrash(emailId) {
            fetch('dashboard.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=trash&email_id=${emailId}`
            }).then(() => location.reload());
        }
    </script>
</body>
</html>
