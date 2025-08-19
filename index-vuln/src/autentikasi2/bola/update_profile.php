<?php
// File: /api/update_profile.php
// Target untuk Mass Assignment

session_start();

// Profil pengguna saat ini (dummy)
if (!isset($_SESSION['profile'])) {
    $_SESSION['profile'] = ['username' => 'charlie', 'is_admin' => false, 'email' => 'charlie@example.com'];
}

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // VULNERABILITY (Mass Assignment):
    // Aplikasi secara membabi buta menggabungkan semua data dari input ke data sesi.
    // Penyerang bisa mengirimkan {"is_admin": true} untuk menaikkan hak aksesnya.
    foreach ($input as $key => $value) {
        // Seharusnya ada whitelist field yang boleh diubah, misal: ['email', 'full_name']
        // Tetapi di sini, semua field bisa diubah.
        $_SESSION['profile'][$key] = $value;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated.',
        'new_profile' => $_SESSION['profile']
    ]);

} else {
    // Untuk GET, hanya tampilkan profil saat ini
    echo json_encode($_SESSION['profile']);
}
?>
