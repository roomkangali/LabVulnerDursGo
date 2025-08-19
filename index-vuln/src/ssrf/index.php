<?php
// ssrf.php
// This script simulates a classic Server-Side Request Forgery (SSRF) vulnerability.
// It takes a URL from the 'url' GET parameter and fetches its contents.
$url = $_GET['url'] ?? '';
$output = '';
if ($url) {
    // No validation or sanitization - vulnerable to SSRF.
    // An attacker can use this to make the server request internal resources.
    $context = stream_context_create(['http' => ['timeout' => 3]]);
    $output = @file_get_contents($url, false, $context);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSRF Vulnerability Lab</title>
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
            background-color: #4f46e5; /* Indigo color */
            color: white;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .output-box {
            background-color: #1f2937; /* Dark gray */
            color: #d1d5db; /* Light gray text */
            padding: 1.5rem;
            border-radius: 0.5rem;
            word-break: break-all;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="container space-y-8">
        <!-- Header -->
        <div class="header">
            <h1 class="text-3xl font-bold">SSRF Vulnerability Lab</h1>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
        </div>

        <!-- Lab Description -->
        <div class="section space-y-4">
            <h2 class="text-2xl font-bold text-gray-800">Lab Description</h2>
            <p class="text-gray-600">
                This lab simulates a <strong>Server-Side Request Forgery (SSRF)</strong> vulnerability. The application fetches the content of a URL provided by the user and displays it.
            </p>
            <p class="text-gray-600">
                Because there is no validation on the input URL, an attacker can force the server to make requests to internal-only resources, such as `http://localhost`, cloud metadata endpoints, or other servers on the internal network.
            </p>
        </div>

        <!-- Testing Area -->
        <div class="section space-y-6">
            <h2 class="text-2xl font-bold text-gray-800">Test the Vulnerability</h2>
            <p class="text-gray-600">
                Enter a URL below. Try a public URL first, then attempt to access an internal resource like `http://localhost` or `file:///etc/passwd`.
            </p>
            
            <form method="get" action="index.php" class="space-y-4">
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700">URL to Fetch</label>
                    <div class="mt-1">
                        <input type="text" name="url" id="url" value="https://example.com" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-3 transition-colors duration-200">
                    </div>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    Fetch Content
                </button>
            </form>

            <?php if ($output !== ''): ?>
                <div>
                    <h3 class="text-lg font-medium text-gray-800">Fetched Content:</h3>
                    <div class="mt-2 output-box"><?= htmlspecialchars($output) ?></div>
                </div>
            <?php elseif (isset($_GET['url'])): ?>
                 <div>
                    <h3 class="text-lg font-medium text-gray-800">Fetched Content:</h3>
                    <div class="mt-2 output-box">[No content returned or an error occurred]</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
