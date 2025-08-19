<?php
session_start(); // Sesi mungkin tidak relevan di sini karena tidak ada validasi token

$response_message = "No action taken on vulnerable endpoint.";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek tombol submit mana yang ditekan (meskipun validasi token diabaikan)
    if (isset($_POST['update_email_vulnerable']) || isset($_POST['update_email_no_token'])) {
        // TIDAK ADA VALIDASI TOKEN CSRF (atau validasi yang sangat lemah/salah)
        $email = htmlspecialchars($_POST['email'] ?? 'N/A');
        
        if (isset($_POST['update_email_vulnerable'])) {
            $submitted_decoy_token = $_POST['csrf_token'] ?? 'none';
             $response_message = "SUCCESS: [Vulnerable Action with Decoy Token] Email updated to " . $email . ". Submitted decoy/ignored token: ".$submitted_decoy_token;
        } elseif (isset($_POST['update_email_no_token'])) {
             $response_message = "SUCCESS: [Vulnerable Action with No Token Field] Email updated to " . $email . ". No token field was present in form.";
        }
        // Di aplikasi nyata, lakukan update email di sini
    } else {
        $response_message = "ERROR: [Vulnerable Action] No valid submit action specified.";
    }
} else {
    $response_message = "ERROR: [Vulnerable Action] Invalid request method.";
    http_response_code(405); // Method Not Allowed
}

// Redirect kembali ke halaman utama dengan pesan
header("Location: csrf_test_home.php?message=" . urlencode($response_message));
exit;
?>
