<?php
// File: /autentikasi2/dashboard.php
require_once '_auth_check.php'; // Pemeriksaan token
require_once '_header.php';     // Tampilan header
?>

<h1>Selamat Datang di Dashboard</h1>
<p>Halo! Anda berhasil terautentikasi menggunakan token statis.</p>
<p>Dari sini, Anda dapat mengakses berbagai halaman pengujian yang rentan. Setiap tautan di bawah ini secara otomatis membawa token sesi Anda.</p>

<div class="link-grid">
    <a href="csrf/csrf_test_home.php?token=<?= urlencode($static_token) ?>" class="link-card">
        <h3>CSRF Test</h3>
        <p>Uji kerentanan Cross-Site Request Forgery.</p>
    </a>
    <a href="fileupload/upload.php?token=<?= urlencode($static_token) ?>" class="link-card">
        <h3>File Upload Test</h3>
        <p>Uji kerentanan Unrestricted File Upload.</p>
    </a>
    <a href="bola/users.php/1?token=<?= urlencode($static_token) ?>" class="link-card">
        <h3>BOLA Test</h3>
        <p>Uji Broken Object Level Authorization.</p>
    </a>
    <a href="massassignment/mass-assignment.php?token=<?= urlencode($static_token) ?>" class="link-card">
        <h3>Mass Assignment Test</h3>
        <p>Uji kerentanan Mass Assignment.</p>
    </a>
</div>

<?php
require_once '_footer.php'; // Tampilan footer
?>
