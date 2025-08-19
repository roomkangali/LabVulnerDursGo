<?php
// File: /autentikasi2/_auth_check.php (Diperbarui dengan Logika Hibrida)

$static_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6ImR1cnNnbyIsInVzZXJfaWQiOjk5LCJpYXQiOjE1MTYyMzkwMjJ9.D-a2iYv03DEbOFpS42d_F5M9h3GZ_s5k7xo5G2jF8_8";

$provided_token = '';

// PERUBAHAN: Sekarang kita memeriksa HTTP Header terlebih dahulu, lalu parameter URL sebagai fallback.
$headers = getallheaders();

if (isset($headers['X-Auth-Token'])) {
    // Prioritas 1: Ambil token dari header (untuk DursGo)
    $provided_token = $headers['X-Auth-Token'];
} elseif (isset($_REQUEST['token'])) {
    // Prioritas 2: Ambil token dari parameter GET/POST (untuk klik manual di browser)
    $provided_token = $_REQUEST['token'];
}

if ($provided_token !== $static_token) {
    // Jika token salah atau tidak ada sama sekali, tolak akses.
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>403 Forbidden - Invalid or Missing Token</h1>";
    exit;
}

// Jika token valid, lanjutkan eksekusi skrip yang memanggilnya.
?>
