<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exposed Files & Directories Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
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
            background-color: #ca8a04; /* Amber color */
            color: white;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .code-box {
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
            <h1 class="text-3xl font-bold">Exposed Files & Directories Lab</h1>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
            </svg>
        </div>

        <!-- Lab Description -->
        <div class="section space-y-4">
            <h2 class="text-2xl font-bold text-gray-800">Lab Description</h2>
            <p class="text-gray-600">
                This lab is designed to test the `exposed` scanner module. The goal is to find sensitive files and directories that are publicly accessible on the web server but should not be.
            </p>
            <p class="text-gray-600">
                The DursGo scanner will use a list of common sensitive paths (e.g., `.env`, `config.php`, `.git/`) to probe the server. You should manually place some of these files and directories inside the `/exposed/` directory on your web server to test the scanner's detection capabilities.
            </p>
        </div>

        <!-- Testing Info -->
        <div class="section space-y-6">
            <h2 class="text-2xl font-bold text-gray-800">How to Test</h2>
            <p class="text-gray-600">
                There is nothing to interact with on this page. The test is performed by running the DursGo scanner against the parent directory where you place your sensitive files.
            </p>
            <p class="text-gray-600">
                Example command to run the scan:
            </p>
            <div class="code-box">
                ./dursgo -u -u http://server/exposed/ -c 10 -r 3 -s exposed
            </div>
        </div>
    </div>

</body>
</html>
