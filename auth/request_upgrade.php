<?php
require_once 'session_config.php';
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'You must be logged in.']);
    exit;
}

if ($_SESSION['account_level'] !== 'free_user') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Only free users can request an upgrade.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET account_level = 'pending_upgrade' WHERE id = ? AND account_level = 'free_user'");
    $stmt->execute([$_SESSION['id']]);
    
    $_SESSION['account_level'] = 'pending_upgrade';

    echo json_encode(['success' => true, 'message' => 'Upgrade request submitted successfully!']);

} catch (PDOException $e) {
    error_log("Upgrade Request Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A server error occurred.']);
}
?>
