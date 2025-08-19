<?php
// app.php - Server-side endpoint to validate JWT and serve dashboard content.

// --- JWT Validation ---
$static_jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c";
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

$token = null;
if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    $token = $matches[1];
}

if ($token && $token === $static_jwt) {
    // --- Token is valid, serve the protected content ---
    // The payload of this token is: {"sub":"1234567890","name":"John Doe","iat":1516239022}
    $username = "John Doe"; // Extracted from token payload in a real scenario

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
            <button id="logoutButton" class="py-2 px-4 bg-red-600 hover:bg-red-700 rounded-md shadow-sm font-medium">Log Out</button>
        </div>
        
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-2xl font-semibold mb-4">Welcome, <span class="text-indigo-400">{$username}</span>!</h2>
            <p class="text-gray-300">You have successfully authenticated using a JWT.</p>
            <div class="mt-6 p-4 bg-gray-700 rounded-md text-sm text-gray-200" style="word-break: break-all;">
                <h3 class="font-semibold mb-2">Your Token:</h3>
                <p id="token-display"></p>
            </div>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-4">Available Labs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="massassignment.php" class="block p-4 bg-gray-700 hover:bg-gray-600 rounded-md transition">
                    <h3 class="font-semibold text-lg">Mass Assignment Lab</h3>
                    <p class="text-gray-400">Explore mass assignment vulnerabilities.</p>
                </a>
                <a href="bola.php" class="block p-4 bg-gray-700 hover:bg-gray-600 rounded-md transition">
                    <h3 class="font-semibold text-lg">BOLA Lab</h3>
                    <p class="text-gray-400">Explore Broken Object Level Authorization.</p>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('logoutButton').addEventListener('click', function() {
            localStorage.removeItem('jwt_token');
            window.location.href = 'login.php';
        });
        
        // Display the token from local storage
        const token = localStorage.getItem('jwt_token');
        if (token) {
            document.getElementById('token-display').textContent = token;
        }
    </script>
</body>
</html>
HTML;

} else {
    // --- Token is invalid or not provided ---
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Authentication failed: Invalid token.']);
}
?>
