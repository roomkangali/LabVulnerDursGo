<?php
// massassignment.php
// This is a protected resource that requires a valid JWT Bearer token
// and is vulnerable to Mass Assignment.

// --- Token Validation ---
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = null;
if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    $token = $matches[1];
}
$valid_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c";
if ($token !== $valid_token) {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Access denied. Invalid or missing Bearer token.']);
    exit();
}
// --- End Token Validation ---

// --- Mass Assignment Vulnerability Logic ---
session_start();

// Initialize a user profile in the session if it doesn't exist.
if (!isset($_SESSION['user_profile_ma'])) {
    $_SESSION['user_profile_ma'] = [
        'user_id'   => 101,
        'username'  => 'dursgo_user',
        'email'     => 'user@example.com',
        'role'      => 'user',
        'is_admin'  => false, // This is the field an attacker would target.
    ];
}

header("Content-Type: application/json");

// If the request is POST or PUT, update the profile.
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        // The vulnerability: blindly merging all user-supplied data into the session.
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
    // For GET requests, just show the current profile.
    echo json_encode($_SESSION['user_profile_ma']);
}
?>
