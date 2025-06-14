<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo "<script>window.location.href='dashboard.php';</script>";
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Login</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f0f0f0; }
        .container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { text-align: center; color: #202124; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #dfe1e5; border-radius: 4px; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1669c0; }
        .error { color: red; text-align: center; }
        a { text-decoration: none; color: #1a73e8; display: block; text-align: center; margin-top: 10px; }
        @media (max-width: 600px) { .container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gmail Clone</h1>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <button type="submit">Login</button>
            <a href="signup.php">Create an account</a>
        </form>
    </div>
    <script>
        if (window.location.search.includes('logout')) {
            alert('Logged out successfully');
        }
    </script>
</body>
</html>
