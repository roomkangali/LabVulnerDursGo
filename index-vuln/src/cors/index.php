<?php
// cors.php
// This script simulates a critical CORS (Cross-Origin Resource Sharing) misconfiguration.
// It is designed to be detected by the DursGo CORS scanner.

// The vulnerability lies here. The server reflects any Origin header it receives.
// This allows any external website to make requests and read the response.
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // 1. Reflect the received Origin header directly.
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");

    // 2. Always allow credentials (a dangerous combination with a reflected origin).
    // This allows an attacker's site to send cookies and read the response.
    header("Access-Control-Allow-Credentials: true");
}

// Set the content type and send a simple JSON response with some dummy data.
header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "message" => "This is a vulnerable CORS API endpoint.",
    "user_data" => [
        "username" => "admin_user",
        "email" => "admin@example.com",
        "session_id" => "xyz789-abc123-secret-session"
    ]
]);
?>
