<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_email = $_POST['recipient'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $action = $_POST['action'];
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$recipient_email]);
    $recipient = $stmt->fetch();
    
    if ($recipient) {
        $folder = $action == 'send' ? 'sent' : 'drafts';
        $stmt = $pdo->prepare("INSERT INTO emails (sender_id, recipient_id, subject, body, folder) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $recipient['id'], $subject, $body, $folder]);
        if ($action == 'send') {
            $stmt = $pdo->prepare("INSERT INTO emails (sender_id, recipient_id, subject, body, folder) VALUES (?, ?, ?, ?, 'inbox')");
            $stmt->execute([$user_id, $recipient['id'], $subject, $body]);
        }
        echo "<script>window.location.href='dashboard.php';</script>";
    } else {
        $error = "Recipient not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Compose</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f0f0; }
        .container { max-width: 600px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #202124; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #dfe1e5; border-radius: 4px; font-size: 16px; }
        textarea { height: 300px; resize: vertical; }
        button { padding: 10px 20px; background: #1a73e8; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #1669c0; }
        .error { color: red; }
        @media (max-width: 600px) { .container { margin: 10px; padding: 15px; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Message</h2>
        <form method="POST">
            <input type="email" name="recipient" placeholder="To" required>
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="body" placeholder="Compose email" required></textarea>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <button type="submit" name="action" value="send">Send</button>
            <button type="submit" name="action" value="draft">Save Draft</button>
            <button type="button" onclick="window.location.href='dashboard.php'">Cancel</button>
        </form>
    </div>
</body>
</html>
