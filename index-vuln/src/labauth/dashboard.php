<?php
// dashboard.php - Server-side page to validate JWT and serve dashboard content.

// This page is intended to be accessed via a POST request from login.php
// However, to make it more robust for direct access (e.g., refresh),
// we'll use a session to persist the token.

session_start();

// --- Static JWT for validation ---
$static_jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c";

$token = null;

// Priority 1: Check for Authorization header (for scanner/API access)
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    $token = $matches[1];
}

// Priority 2: Check for token from POST (from login form)
if (!$token && isset($_POST['jwt_token'])) {
    $token = $_POST['jwt_token'];
    $_SESSION['jwt_token'] = $token; // Store in session for subsequent requests
}

// Priority 3: Check for token in session (for page refreshes)
if (!$token && isset($_SESSION['jwt_token'])) {
    $token = $_SESSION['jwt_token'];
}

// --- Token Validation ---
if ($token && $token === $static_jwt) {
    // --- Token is valid, serve the protected content ---
    $username = "John Doe"; // From token payload in a real scenario

    // Output the dashboard HTML
    header('Content-Type: text/html');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JWT Lab - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Dark background */
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <a href="login.php" class="py-2 px-4 bg-red-600 hover:bg-red-700 rounded-md shadow-sm font-medium">Log Out</a>
        </div>
        
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-2xl font-semibold mb-4">Welcome, <span class="text-indigo-400">{$username}</span>!</h2>
            <p class="text-gray-300">You have successfully authenticated using a JWT.</p>
            <div class="mt-6 p-4 bg-gray-700 rounded-md text-sm text-gray-200" style="word-break: break-all;">
                <h3 class="font-semibold mb-2">Your Token:</h3>
                <p>{$token}</p>
            </div>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-4">Available Labs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="massassignment.php" class="block p-4 bg-gray-700 hover:bg-gray-600 rounded-md transition">
                    <h3 class="font-semibold text-lg">Mass Assignment Lab</h3>
                    <p class="text-gray-400">Explore mass assignment vulnerabilities.</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

} else {
    // --- Token is invalid or not provided ---
    session_destroy(); // Clear session on failure
    header('Content-Type: text/html');
    http_response_code(401); // Unauthorized
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center p-8 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-red-600">Access Denied</h2>
        <p class="mt-2 text-gray-700">No valid token provided. Please log in first.</p>
        <a href="login.php" class="mt-4 inline-block py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Go to Login</a>
    </div>
</body>
</html>
HTML;
}
?>
