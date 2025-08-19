<?php
// File: /autentikasi2/_header.php
// Pastikan _auth_check.php sudah dipanggil sebelum ini.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DursGo Testbed</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; background-color: #f4f7f9; }
        .navbar { background-color: #fff; padding: 0 40px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #555; text-decoration: none; padding: 20px 15px; display: inline-block; font-weight: 500; }
        .navbar a.logo { font-weight: 700; color: #333; font-size: 1.2em; padding-left:0; }
        .navbar a:hover { color: #007bff; }
        .main-content { padding: 40px; max-width: 960px; margin: 0 auto; }
        .box { background:#fff; padding:30px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        h1, h2 { margin-top: 0; }
        .link-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .link-card { display: block; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none; color: #333; transition: transform 0.2s, box-shadow 0.2s; }
        .link-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .link-card h3 { margin-top: 0; color: #007bff; }
    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <a href="../dashboard.php?token=<?= urlencode($static_token) ?>" class="logo">DursGo Testbed</a>
            <a href="../csrf/csrf_test_home.php?token=<?= urlencode($static_token) ?>">CSRF</a>
            <a href="../fileupload/upload.php?token=<?= urlencode($static_token) ?>">File Upload</a>
            <a href="../bola/users.php/1?token=<?= urlencode($static_token) ?>">BOLA</a>
            <a href="../massassignment/mass-assignment.php?token=<?= urlencode($static_token) ?>">Mass Assignment</a>
        </div>
        <div>
            <a href="/autentikasi2/login.php">Logout</a>
        </div>
    </div>
    <div class="main-content">
