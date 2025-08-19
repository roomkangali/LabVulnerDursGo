<?php
// auth.php
// This script acts as a simple API endpoint to issue a static JWT.

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit();
}

// Get the raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// --- Static Credentials Check ---
// In a real application, you would check this against a database.
$valid_username = 'john.doe';
$valid_password = 'secret123';

if (isset($data['username']) && isset($data['password']) && $data['username'] === $valid_username && $data['password'] === $valid_password) {
    // --- Static JWT ---
    // This is the exact same token from the DursGo config example.
    // In a real app, this would be dynamically generated and signed.
    $static_jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c";

    http_response_code(200); // OK
    echo json_encode(['token' => $static_jwt]);
} else {
    // Invalid credentials
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Invalid username or password.']);
}
?>
