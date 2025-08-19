<?php
// Dynamically determine the server's hostname to create portable links.
$server_host = $_SERVER['SERVER_NAME'] ?? $_SERVER['SERVER_ADDR'] ?? 'localhost';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DursGo Vulnerability Lab Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        .header {
            background-color: #111827; /* Dark Gray */
            color: white;
            padding: 2.5rem;
            border-radius: 1rem;
            text-align: center;
        }
        .lab-card {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .lab-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .lab-card h3 {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1f2937;
        }
        .lab-card p {
            color: #6b7280;
            margin-top: 0.5rem;
        }
        .lab-card a {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #374151;
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .lab-card a:hover {
            background-color: #1f2937;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">

    <div class="container space-y-10">
        <!-- Header -->
        <div class="header">
            <h1 class="text-4xl font-bold">DursGo Vulnerability Labs</h1>
            <p class="mt-2 text-lg text-gray-300">A collection of vulnerable applications to test the DursGo scanner.</p>
        </div>

        <!-- Lab Links Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Open Redirect Lab -->
            <div class="lab-card">
                <h3>Open Redirect Lab</h3>
                <p>A lab demonstrating a classic Open Redirect vulnerability where the application redirects to any user-supplied URL.</p>
                <a href="/openredirect/">Go to Lab</a>
            </div>

            <!-- Blind Vulnerabilities Lab -->
            <div class="lab-card">
                <h3>Blind Vulnerabilities Lab</h3>
                <p>Contains both Blind SSRF and Blind Command Injection vulnerabilities, detectable via OAST.</p>
                <a href="/blind/">Go to Lab</a>
            </div>
            <!-- In-Band SSRF Lab -->
            <div class="lab-card">
                <h3>SSRF (In-Band) Lab</h3>
                <p>A classic SSRF where the application fetches and displays content from a user-supplied URL.</p>
                <a href="/ssrf/">Go to Lab</a>
            </div>

            <!-- In-Band Mass Assignment Lab -->
            <div class="lab-card">
                <h3>Mass Assignment Lab</h3>
                <p>This link leads to a protected API endpoint vulnerable to Mass Assignment.</p>
                <a href="/labauth">Go to Lab</a>
            </div>

            <!-- CORS Lab -->
            <div class="lab-card">
                <h3>CORS Misconfiguration Lab</h3>
                <p>An API endpoint that improperly reflects the Origin header, allowing data theft from any domain.</p>
                <a href="/cors/">Go to Lab</a>
            </div>

            <!-- Authentication Lab -->
            <div class="lab-card">
                <h3>Authentication Lab</h3>
                <p>A login page to test authenticated scanning capabilities and related vulnerabilities.</p>
                <p>VULNERABILITY CSRF - BOLA - FILE UPLOAD - MASSASSIGMENT</p>
                <a href="/autentikasi2/">Go to Lab</a>
            </div>

            <!-- Exposed Files Lab -->
            <div class="lab-card">
                <h3>Exposed Files Lab</h3>
                <p>A directory to test the detection of sensitive files and folders like `.env` or `.git/`.</p>
                <a href="/exposed/">Go to Lab</a>
            </div>

            <!-- IDOR & Stored XSS Lab -->
            <div class="lab-card">
                <h3>IDOR & Stored XSS Lab</h3>
                <p>A login authenticated test for Insecure Direct Object References and Stored Cross-Site Scripting.</p>
                <a href="http://<?= htmlspecialchars($server_host) ?>:5000" target="_blank" rel="noopener noreferrer">Go to Lab (Port 5000)</a>
            </div>

            <!-- GraphQL Lab -->
            <div class="lab-card">
                <h3>GraphQL API Lab</h3>
                <p>A vulnerable GraphQL endpoint to test for introspection, injection, and other API-specific flaws.</p>
                <a href="http://<?= htmlspecialchars($server_host) ?>:4000/graphql" target="_blank" rel="noopener noreferrer">Go to Lab (Port 4000)</a>
            </div>

        </div>
    </div>

</body>
</html>
