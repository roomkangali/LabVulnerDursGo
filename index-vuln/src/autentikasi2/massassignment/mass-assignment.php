<?php
// File: /autentikasi2/massassignment/mass-assignment.php
require_once '../_auth_check.php';
session_start();

if (!isset($_SESSION['user_profile_ma'])) {
    $_SESSION['user_profile_ma'] = [
        'user_id'   => 101,
        'username'  => 'dursgo_user',
        'email'     => 'user@example.com',
        'role'      => 'user',
        'is_admin'  => false,
    ];
}

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $_SESSION['user_profile_ma'][$key] = $value;
        }
    }
    echo json_encode([
        'status'  => 'success',
        'message' => 'Profile updated.',
        'data'    => $_SESSION['user_profile_ma']
    ]);
} else {
    echo json_encode($_SESSION['user_profile_ma']);
}
?>
