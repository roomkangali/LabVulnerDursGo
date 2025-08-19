<?php
// File: /autentikasi2/login.php

// PERUBAHAN: Menggunakan token yang terlihat seperti JWT asli.
$static_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6ImR1cnNnbyIsInVzZXJfaWQiOjk5LCJpYXQiOjE1MTYyMzkwMjJ9.D-a2iYv03DEbOFpS42d_F5M9h3GZ_s5k7xo5G2jF8_8";
$message = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === 'dursgo' && $pass === 'password123') {
        $login_success = true;
        $message = "Login berhasil! Gunakan token JWT dummy di bawah ini untuk autentikasi.";
    } else {
        $message = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Token Login - DursGo Testbed</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 40px; background-color: #f4f7f9; display: flex; justify-content: center; align-items: center; height: 80vh; }
        .container { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); text-align: center; max-width: 450px; }
        h2 { color: #333; }
        form { margin-top: 20px; }
        input { width: calc(100% - 20px); padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ddd; }
        button { width: 100%; padding: 12px; border: none; border-radius: 6px; background-color: #007bff; color: white; font-weight: 500; cursor: pointer; }
        .message { margin-top: 20px; padding: 10px; border-radius: 6px; }
        .message.error { background-color: #f8d7da; color: #721c24; }
        .message.success { background-color: #d4edda; color: #155724; }
        .token-box { margin-top: 15px; padding: 15px; background-color: #e9ecef; border: 1px dashed #ced4da; border-radius: 6px; word-wrap: break-word; text-align: left; }
        a { color: #007bff; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Token Authentication Login</h2>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Username" value="dursgo" required><br>
            <input type="password" name="password" placeholder="Password" value="password123" required><br>
            <button type="submit">Get Token</button>
        </form>

        <?php if ($message): ?>
            <p class="message <?= $login_success ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($login_success): ?>
            <div class="token-box">
                <strong>Your Dummy JWT Token:</strong><br>
                <code><?= $static_token ?></code>
            </div>
            <p><a href="dashboard.php?token=<?= urlencode($static_token) ?>">Proceed to Dashboard &rarr;</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
