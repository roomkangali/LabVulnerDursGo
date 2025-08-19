<?php
// bola.php
// This is a protected resource that requires a valid JWT Bearer token
// and is vulnerable to Broken Object Level Authorization (BOLA).

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

// --- BOLA Vulnerability Logic (from your working users.php) ---
$users = [
    1 => ['id' => 1, 'username' => 'alice', 'email' => 'alice@example.com', 'role' => 'user'],
    2 => ['id' => 2, 'username' => 'bob', 'email' => 'bob@example.com', 'role' => 'user'],
    3 => ['id' => 3, 'username' => 'admin', 'email' => 'admin@example.com', 'role' => 'admin'],
];

header("Content-Type: application/json");

// Assume the authenticated user is 'alice' with user_id = 1.
$current_user_id = 1; 

// Get the requested user ID from the URL path (e.g., /bola.php/2)
$path_info = $_SERVER['PATH_INFO'] ?? '';
$path_parts = explode('/', $path_info);
$requested_id = intval(end($path_parts));

// The vulnerability: The code checks if the requested_id exists,
// but it *never* checks if $current_user_id is allowed to view it.
if ($requested_id > 0) {
    if (isset($users[$requested_id])) {
        // Vulnerable: Returns any user's data as long as the ID exists.
        echo json_encode($users[$requested_id]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} else {
    // Default action: list all users (also a potential vulnerability).
    echo json_encode(array_values($users));
}
?>
