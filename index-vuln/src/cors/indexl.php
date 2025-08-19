<?php
// cors.php
// This script simulates a CORS (Cross-Origin Resource Sharing) misconfiguration.

// This block acts as the vulnerable API endpoint.
// It's triggered when the '?api=true' query parameter is present.
if (isset($_GET['api'])) {
    // The vulnerability lies here. The server reflects any Origin header it receives.
    // This allows any external website to make requests and read the response.
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // 1. Reflect the received Origin header directly.
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");

        // 2. Always allow credentials (a dangerous combination with a reflected origin).
        // This allows the attacker's site to send cookies and read the response.
        header("Access-Control-Allow-Credentials: true");
    }

    // Set the content type and send a simple JSON response with some dummy data.
    header("Content-Type: application/json");

    echo json_encode([
        "status" => "success",
        "message" => "Data fetched successfully.",
        "user_data" => [
            "username" => "admin_user",
            "email" => "admin@example.com",
            "session_id" => "xyz789-abc123-secret-session"
        ]
    ]);
    exit(); // Stop execution to not render the HTML below.
}

// The rest of the file is the HTML UI for the lab page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CORS Vulnerability Lab</title>
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
            background-color: #059669; /* Emerald color */
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
            <h1 class="text-3xl font-bold">CORS Vulnerability Lab</h1>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.82m5.84-2.56v4.82a6 6 0 01-1.242 3.63m0-3.63a6 6 0 00-1.242-3.63m0 3.63l-1.242-3.63m1.242 3.63l-1.242-3.63M3.102 8.652a6 6 0 015.84-7.38v4.82m-5.84 2.56v-4.82a6 6 0 011.242-3.63m0 3.63a6 6 0 001.242 3.63m0-3.63l1.242 3.63m-1.242-3.63l1.242 3.63" />
            </svg>
        </div>

        <!-- Lab Description -->
        <div class="section space-y-4">
            <h2 class="text-2xl font-bold text-gray-800">Lab Description</h2>
            <p class="text-gray-600">
                This lab demonstrates a critical <strong>CORS (Cross-Origin Resource Sharing)</strong> misconfiguration. The API endpoint on this page reflects any `Origin` header it receives in the `Access-Control-Allow-Origin` response header and also sets `Access-Control-Allow-Credentials` to `true`.
            </p>
            <p class="text-gray-600">
                This vulnerability allows any website on the internet to make a request to this endpoint, send the user's cookies, and read the sensitive data in the response.
            </p>
        </div>

        <!-- Testing Area -->
        <div class="section space-y-6">
            <h2 class="text-2xl font-bold text-gray-800">Test the Vulnerability</h2>
            <p class="text-gray-600">
                Click the button below to simulate a cross-origin request. The JavaScript on this page will fetch data from the vulnerable API endpoint (`cors.php?api=true`) and display the result.
            </p>
            
            <button id="fetchButton" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
                Fetch Data from API
            </button>

            <div id="resultContainer" style="display: none;">
                <h3 class="text-lg font-medium text-gray-800">Fetched Content:</h3>
                <div id="output" class="mt-2 output-box"></div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('fetchButton').addEventListener('click', function() {
            // Fetch data from the vulnerable API endpoint on this same page.
            // 'credentials: "include"' simulates sending cookies with the request.
            fetch('cors.php?api=true', {
                method: 'GET',
                headers: {
                    // The browser automatically adds the 'Origin' header for cross-origin requests.
                    // For a same-origin request like this, we don't need to set it manually.
                    // The DursGo scanner will set it to test for the vulnerability.
                },
                credentials: 'include' 
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('output').textContent = JSON.stringify(data, null, 2);
                document.getElementById('resultContainer').style.display = 'block';
            })
            .catch(error => {
                document.getElementById('output').textContent = 'Error fetching data: ' + error;
                document.getElementById('resultContainer').style.display = 'block';
            });
        });
    </script>

</body>
</html>
