<?php require_once 'auth/session_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-xs">
        <form action="auth/login.php" method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h1 class="text-2xl font-bold mb-4 text-center">Login</h1>
            <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 hidden"><span class="block sm:inline"></span></div>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="email" type="email" name="email" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="password" type="password" name="password" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">Sign In</button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.html">Create Account</a>
            </div>
            <div class="text-center mt-4"><a class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-blue-800" href="forgot_password.html">Forgot Password?</a></div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const error = params.get('error');
            if (error) {
                const errorDiv = document.getElementById('error-message');
                const errorMessageSpan = errorDiv.querySelector('span');
                let message = 'An unknown error occurred.';
                switch (error) {
                    case 'invalid_input': message = 'Please enter a valid email and password.'; break;
                    case 'not_verified': message = 'Please verify your email before logging in.'; break;
                    case 'pending_approval': message = 'Your account is awaiting admin approval.'; break;
                    case 'banned': message = 'Your account has been banned.'; break;
                    case 'auth_failed': message = 'Incorrect email or password.'; break;
                    case 'server_error': message = 'A server error occurred. Please try again later.'; break;
                    case 'too_many_attempts': message = 'Too many login attempts. Please wait 15 minutes.'; break;
                }
                errorMessageSpan.textContent = message;
                errorDiv.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
