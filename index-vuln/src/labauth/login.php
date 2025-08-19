<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JWT Authentication Lab - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Dark background */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-900">JWT Authentication Lab</h2>
        
        <form id="loginForm" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <div class="mt-1">
                    <input id="username" name="username" type="text" required value="john.doe"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" required value="secret123"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Log In & Get Token
                </button>
            </div>
        </form>
        
        <div id="result" class="mt-6 p-4 bg-gray-100 rounded-md text-sm text-gray-700" style="display: none; word-break: break-all;"></div>
        <div id="dashboardLinkContainer" class="mt-4 text-center" style="display: none;">
            <a href="dashboard.php" id="dashboardLink" class="font-medium text-indigo-600 hover:text-indigo-500">Go to Dashboard (will fail without token)</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const resultDiv = document.getElementById('result');
            const dashboardLinkContainer = document.getElementById('dashboardLinkContainer');

            fetch('auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username: username, password: password }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    // Show success message and redirect
                    resultDiv.innerHTML = '<strong>Success!</strong> Redirecting to dashboard...';
                    resultDiv.style.display = 'block';
                    
                    // Function to POST to the dashboard
                    function postToDashboard(token) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'dashboard.php';

                        const hiddenField = document.createElement('input');
                        hiddenField.type = 'hidden';
                        hiddenField.name = 'jwt_token';
                        hiddenField.value = token;

                        form.appendChild(hiddenField);
                        document.body.appendChild(form);
                        form.submit();
                    }

                    setTimeout(() => {
                        postToDashboard(data.token);
                    }, 1500); // Redirect after 1.5 seconds
                } else {
                    resultDiv.textContent = 'Error: ' + (data.error || 'Invalid credentials.');
                    resultDiv.style.display = 'block';
                }
            })
            .catch(error => {
                resultDiv.textContent = 'An unexpected error occurred.';
                resultDiv.style.display = 'block';
            });
        });
    </script>

</body>
</html>
