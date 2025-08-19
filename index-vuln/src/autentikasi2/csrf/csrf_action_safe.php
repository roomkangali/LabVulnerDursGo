<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_email_safe'])) {
        $submitted_token = $_POST['csrf_token'] ?? ''; // Ambil token yang dikirim
        $session_token = $_SESSION['csrf_real_token'] ?? ''; // Ambil token dari sesi

        if (!empty($submitted_token) && !empty($session_token) && hash_equals($session_token, $submitted_token)) {
            // Token valid
            $email = htmlspecialchars($_POST['email'] ?? 'N/A');
            $response_message = "SUCCESS: [Safe Action] Email updated to " . $email . " with valid token.";

            // Redirect dengan pesan sukses
            header("Location: csrf_test_home.php?message=" . urlencode($response_message));
            exit;
        } else {
            // ❌ Token tidak valid
            http_response_code(403);
            echo "<h1>403 Forbidden</h1>";
            echo "<p>ERROR: [Safe Action] Invalid or missing CSRF token. Action denied.</p>";
            exit; // ⚠️ Hentikan eksekusi total, jangan redirect
        }
    } else {
        // Submit key tidak sesuai
        http_response_code(400);
        echo "<h1>400 Bad Request</h1>";
        echo "<p>ERROR: [Safe Action] No valid submit action specified.</p>";
        exit;
    }
} else {
    // Method bukan POST
    http_response_code(405); // Method Not Allowed
    echo "<h1>405 Method Not Allowed</h1>";
    echo "<p>ERROR: [Safe Action] Invalid request method.</p>";
    exit;
}
