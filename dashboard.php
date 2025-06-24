<?php
    require_once 'auth/session_config.php';
    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }
    require_once 'auth/db_connect.php';

    // Fetch user's bandwidth usage for today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT usage_bytes FROM bandwidth_usage WHERE user_id = ? AND date = ?");
    $stmt->execute([$_SESSION['id'], $today]);
    $usage_today = $stmt->fetchColumn() ?: 0;

    $usage_mb = round($usage_today / 1024 / 1024, 2);
    $limit_mb = round(FREE_USER_BANDWIDTH_LIMIT_BYTES / 1024 / 1024, 0);
    $usage_percent = ($usage_today > 0) ? min(100, ($usage_today / FREE_USER_BANDWIDTH_LIMIT_BYTES) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 md:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">My Dashboard</h1>
            <a href="index.php" class="text-blue-500 hover:text-blue-700">&larr; Back to Browser</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Account Information</h2>
                <div class="space-y-3">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p><strong>Account Tier:</strong> <span class="font-semibold text-indigo-600 capitalize"><?php echo str_replace('_', ' ', htmlspecialchars($_SESSION['account_level'])); ?></span></p>
                </div>
            </div>

            <div id="status-card">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Account Status</h2>
                <?php if ($_SESSION['account_level'] === 'free_user'): ?>
                    <div class="space-y-4">
                        <p>You are on the <strong>Free Plan</strong> with a daily limit of <?php echo $limit_mb; ?>MB.</p>
                        <div>
                            <label for="bandwidth">Today's Bandwidth Usage:</label>
                            <div class="w-full bg-gray-200 rounded-full h-4 mt-1">
                                <div class="bg-blue-500 h-4 rounded-full" style="width: <?php echo $usage_percent; ?>%"></div>
                            </div>
                            <p class="text-sm text-gray-600 text-right"><?php echo $usage_mb; ?> / <?php echo $limit_mb; ?> MB used</p>
                        </div>
                        <button id="upgrade-btn" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            Upgrade to Paid Plan (Unlimited)
                        </button>
                    </div>
                <?php elseif ($_SESSION['account_level'] === 'pending_upgrade'): ?>
                    <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                        <p class="font-bold">Upgrade Pending</p>
                        <p>Your request to upgrade has been submitted and is awaiting admin approval.</p>
                    </div>
                <?php elseif ($_SESSION['account_level'] === 'paid_user' || $_SESSION['account_level'] === 'admin'): ?>
                     <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                        <p class="font-bold">Premium Plan Active</p>
                        <p>You have unlimited bandwidth. Thank you for your support!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        const upgradeButton = document.getElementById('upgrade-btn');
        if (upgradeButton) {
            upgradeButton.addEventListener('click', async () => {
                if (!confirm('Are you sure you want to request an account upgrade?')) return;
                try {
                    const response = await fetch('auth/request_upgrade.php', { method: 'POST' });
                    const result = await response.json();
                    if (result.success) {
                        Toastify({ text: result.message, duration: 3000, style: { background: "green" }}).showToast();
                        document.getElementById('status-card').innerHTML = `
                             <h2 class="text-2xl font-semibold text-gray-700 mb-4">Account Status</h2>
                             <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                                <p class="font-bold">Upgrade Pending</p>
                                <p>Your request to upgrade has been submitted and is awaiting admin approval.</p>
                            </div>
                        `;
                    } else {
                         Toastify({ text: result.error, duration: 3000, style: { background: "red" }}).showToast();
                    }
                } catch(e) {
                    Toastify({ text: 'A network error occurred.', duration: 3000, style: { background: "red" }}).showToast();
                }
            });
        }
    </script>
</body>
</html>
