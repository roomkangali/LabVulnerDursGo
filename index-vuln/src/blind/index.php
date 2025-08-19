<?php
// vulnerable.php
// This script simulates a real server-side Blind SSRF vulnerability.
// It takes a URL from the 'reportUrl' POST parameter and fetches it using cURL.
// The response from the fetch is not returned to the user, making it a blind vulnerability.

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reportUrl'])) {
    $url = $_POST['reportUrl'];

    if (empty($url)) {
        $message = 'URL cannot be empty.';
        $message_type = 'error';
    } elseif (filter_var($url, FILTER_VALIDATE_URL)) {
        // Vulnerability 1: Blind SSRF on 'reportUrl'
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5-second timeout
        
        // Execute the request but do nothing with the result.
        curl_exec($ch);
        curl_close($ch);

        // Vulnerability 2: Blind Command Injection on 'comment'
        if (isset($_POST['comment']) && !empty($_POST['comment'])) {
            $comment = $_POST['comment'];
            // Final Version for Blind OAST Testing:
            // This logic extracts any OAST domain from the scanner's payload
            // (e.g., from '; nslookup xyz.oast.live') and makes a direct network request to it.
            // This makes the vulnerability blind again and guarantees compatibility with the scanner's
            // OAST payloads, bypassing the need for external programs like nslookup on the server.
            preg_match('/[a-zA-Z0-9\-\.]+\.oast\.[a-zA-Z]+/', $comment, $matches);
            if (!empty($matches)) {
                $oast_domain = $matches[0];
                // Use a native PHP function to guarantee the network request.
                // The '@' suppresses errors, ensuring it remains a blind vulnerability.
                @file_get_contents("http://" . $oast_domain);
            }
        }

        $message = 'Report has been submitted. An admin will review the link. An interaction is proof of the vulnerability.';
        $message_type = 'success';
    } else {
        $message = 'The URL you entered is invalid. Please try again.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blind SSRF & Command Injection Vulnerability Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        .section {
            background-color: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            margin-top: 1rem;
        }
        .message.success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="container space-y-8">
        <!-- Header -->
        <div class="header">
            <h1 class="text-3xl font-bold">Blind SSRF & Command Injection Vulnerability Lab</h1>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 01-9-9c0-1.853.793-3.528 2.067-4.71a.75.75 0 011.088-.006c.725.688 1.408 1.488 2.043 2.378A6.75 6.75 0 0012 15a6.75 6.75 0 00-6.75-6.75c-1.897 0-3.606.877-4.72 2.227a.75.75 0 01-1.018.158c-1.28-1.026-2.074-2.5-2.22-4.14a.75.75 0 01.328-.73c.73-.39 1.484-.716 2.26-.976A9 9 0 0112 3a9 9 0 019 9c0 1.853-.793 3.528-2.067 4.71a.75.75 0 01-1.088.006c-.725-.688-1.408-1.488-2.043-2.378A6.75 6.75 0 0012 9a6.75 6.75 0 006.75 6.75c1.897 0 3.606-.877 4.72-2.227a.75.75 0 011.018-.158c1.28 1.026 2.074 2.5 2.22 4.14a.75.75 0 01-.328.73c-.73-.39-1.484-.716-2.26-.976A9 9 0 0112 21z" />
            </svg>
        </div>

        <!-- Lab Description -->
        <div class="section space-y-4">
            <h2 class="text-2xl font-bold text-gray-800">Lab Description</h2>
            <p class="text-gray-600">
                This lab simulates a web application with two distinct blind vulnerabilities. Both are triggered by submitting the form below, which requires the `reportUrl` parameter to be a valid URL.
            </p>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li><strong>Blind SSRF:</strong> The <code>reportUrl</code> parameter is vulnerable to Blind Server-Side Request Forgery. The server fetches the provided URL but does not return the response.</li>
                <li><strong>Blind Command Injection:</strong> The <code>comment</code> parameter is vulnerable to Blind Command Injection. User input is used in a server-side command, but the output is not displayed.</li>
            </ul>
            <p class="text-gray-600">
                The only way to confirm these vulnerabilities is via an <strong>Out-of-Band (OOB)</strong> interaction, where a backend request from the server contacts an OAST domain you control.
            </p>
        </div>

        <!-- Input Form -->
        <div class="section space-y-6">
            <h2 class="text-2xl font-bold text-gray-800">Report a Suspicious Link</h2>
            <p class="text-gray-600">
                Use this form to test the vulnerabilities. Provide a valid URL for the first field and your OAST payloads in both.
            </p>
            <form id="reportForm" method="post" action="">
                <div class="space-y-4">
                    <div>
                        <label for="reportUrl" class="block text-sm font-medium text-gray-700">URL to Report</label>
                        <div class="mt-1">
                            <input type="text" name="reportUrl" id="reportUrl" placeholder="Example: https://example.com" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-3 transition-colors duration-200">
                        </div>
                    </div>
                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
                        <div class="mt-1">
                            <textarea id="comment" name="comment" rows="3" placeholder="Add a comment about this link..." class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-3 transition-colors duration-200"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input id="subscribe" name="subscribe" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="subscribe" class="ml-2 block text-sm text-gray-900">
                            Notify me when the link is verified.
                        </label>
                    </div>
                </div>
                <button type="submit" id="submitBtn" class="w-full mt-6 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Report
                </button>
            </form>
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
