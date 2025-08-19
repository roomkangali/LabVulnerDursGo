<?php
// index.php
// This script simulates a classic Open Redirect vulnerability.
// It takes a URL from the 'redirect_url' GET parameter and redirects the user to it.

if (isset($_GET['redirect_url']) && !empty($_GET['redirect_url'])) {
    $url = $_GET['redirect_url'];
    // The vulnerable part: redirecting to a user-controlled URL without any validation.
    // A real-world attacker could use this to redirect users to a phishing site.
    header("Location: " . $url);
    exit(); // Ensure no further code is executed after the redirect.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Redirect Vulnerability Lab</title>
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
            background-color: #be123c; /* Rose color for a different look */
            color: white;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .link-box {
            background-color: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            word-break: break-all;
            font-family: monospace;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="container space-y-8">
        <!-- Header -->
        <div class="header">
            <h1 class="text-3xl font-bold">Open Redirect Vulnerability Lab</h1>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 3.03v.568c0 .334.148.65.405.864l1.068.89c.442.369.535 1.01.216 1.49l-.51.766a2.25 2.25 0 01-1.161.886l-.143.048a1.107 1.107 0 00-.57 1.664l.143.258a1.107 1.107 0 001.664.57l.143-.048a2.25 2.25 0 011.161.886l.51.766c.319.48.126 1.121-.216 1.49l-1.068.89a1.125 1.125 0 00-.405.864v.568m-6 0v-.568c0-.334-.148-.65-.405-.864l-1.068-.89a1.125 1.125 0 01-.216-1.49l.51-.766a2.25 2.25 0 001.161-.886l.143-.048a1.107 1.107 0 01.57-1.664l-.143-.258a1.107 1.107 0 01-1.664-.57l-.143.048a2.25 2.25 0 00-1.161-.886l-.51-.766c-.319-.48-.126-1.121.216-1.49l1.068-.89a1.125 1.125 0 00.405-.864v-.568" />
            </svg>
        </div>

        <!-- Lab Description -->
        <div class="section space-y-4">
            <h2 class="text-2xl font-bold text-gray-800">Lab Description</h2>
            <p class="text-gray-600">
                This lab simulates an <strong>Open Redirect</strong> vulnerability. The application takes a URL from a query parameter and redirects the user to that URL without validating if it's a trusted destination.
            </p>
            <p class="text-gray-600">
                An attacker can exploit this by crafting a link using this trusted domain that redirects users to a malicious website, making phishing attacks more convincing.
            </p>
        </div>

        <!-- Testing Area -->
        <div class="section space-y-6">
            <h2 class="text-2xl font-bold text-gray-800">Test the Vulnerability</h2>
            <p class="text-gray-600">
                Enter a URL in the form below to test the redirect functionality. The DursGo scanner should detect this vulnerability.
            </p>
            
            <form method="get" action="index.php" class="space-y-4">
                <div>
                    <label for="redirect_url" class="block text-sm font-medium text-gray-700">URL to Redirect To</label>
                    <div class="mt-1">
                        <input type="text" name="redirect_url" id="redirect_url" value="https://github.com/roomkangali/dursgo" class="shadow-sm focus:ring-rose-500 focus:border-rose-500 block w-full sm:text-sm border-gray-300 rounded-md p-3 transition-colors duration-200">
                    </div>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors duration-200">
                    Test Redirect
                </button>
            </form>
        </div>
    </div>

</body>
</html>
