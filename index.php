<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Browser</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style> 
        body { font-family: 'Noto Sans KR', sans-serif; } 
        #loading-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255, 255, 255, 0.8); z-index: 9999; display: flex; justify-content: center; align-items: center; flex-direction: column; gap: 1rem; } 
        .loader { border: 8px solid #f3f3f3; border-radius: 50%; border-top: 8px solid #3498db; width: 60px; height: 60px; animation: spin 2s linear infinite; } 
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } 
        #browser-view-container { cursor: none; } 
        #mouse-cursor { position: fixed; width: 20px; height: 20px; border: 2px solid white; background-color: #3498db; border-radius: 50%; z-index: 10000; pointer-events: none; transform: translate(-50%, -50%); display: none; transition: width 0.1s, height 0.1s; }
        #mouse-cursor.active { width: 15px; height: 15px; }
    </style>
</head>
<body class="bg-gray-100">
    <?php
        require_once 'auth/session_config.php';
        if (isset($_SESSION['loggedin'])) {
            echo "<script> const userSession = { isLoggedIn: true, accountLevel: '" . htmlspecialchars($_SESSION['account_level'], ENT_QUOTES) . "', sessionId: '" . session_id() . "' }; </script>";
        } else {
            echo "<script> const userSession = { isLoggedIn: false }; </script>";
        }
    ?>
    <div id="app-container" class="container mx-auto p-4" style="display: none;">
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Interactive Server-Side Browser</h1>
                <div>
                    <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">My Dashboard</a>
                    <a id="admin-link" href="admin.html" class="hidden bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Admin Panel</a>
                    <a href="auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Logout</a>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" id="url-input" class="flex-grow p-2 border border-gray-300 rounded-lg" placeholder="https://example.com">
                <button id="go-button" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">이동</button>
            </div>
        </div>
        <div id="browser-view-container" class="relative bg-white rounded-lg shadow-md" style="height: 80vh;">
            <div id="loading-overlay" class="hidden">
                <div class="loader"></div>
                <p class="text-gray-600">Loading page...</p>
            </div>
            <iframe id="browser-view" class="w-full h-full border-0 rounded-lg"></iframe>
        </div>
    </div>
    <div id="mouse-cursor"></div>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        if (typeof userSession !== 'undefined' && userSession.isLoggedIn) {
            document.getElementById('app-container').style.display = 'block';
            connectWebSocket();
            if (userSession.accountLevel === 'admin') document.getElementById('admin-link').classList.remove('hidden');
        } else {
            window.location.href = 'login.php';
        }

        const browserViewContainer = document.getElementById('browser-view-container');
        const mouseCursor = document.getElementById('mouse-cursor');
        browserViewContainer.addEventListener('mouseenter', () => { mouseCursor.style.display = 'block'; });
        browserViewContainer.addEventListener('mouseleave', () => { mouseCursor.style.display = 'none'; });
        browserViewContainer.addEventListener('mousedown', () => mouseCursor.classList.add('active'));
        browserViewContainer.addEventListener('mouseup', () => mouseCursor.classList.remove('active'));

        let socket;
        function connectWebSocket() { /* ... function unchanged, includes Toastify ... */ }
        function sendMessage(data) { /* ... function unchanged ... */ }
        function navigateToUrl(url) { /* ... function unchanged ... */ }
        
        document.getElementById('go-button').addEventListener('click', () => navigateToUrl(document.getElementById('url-input').value.trim()));
        document.getElementById('url-input').addEventListener('keypress', (e) => { if (e.key === 'Enter') navigateToUrl(e.target.value.trim()); });
        
        window.addEventListener('message', (event) => {
            const data = event.data;
            if (!data || !data.type) return;
            if (data.type === 'mouse') {
                mouseCursor.style.left = event.data.x + 'px';
                mouseCursor.style.top = event.data.y + 'px';
                sendMessage(data);
            } else if (['click', 'keyboard', 'scroll'].includes(data.type)) {
                sendMessage(data);
                if(data.type === 'click') document.getElementById('loading-overlay').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
