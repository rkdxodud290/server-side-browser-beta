<?php
// 1. Initialization
require_once '../config.php';
require_once 'session_config.php'; 
require_once 'db_connect.php'; 

// 2. Security Validations
validate_csrf_token($_POST['csrf_token'] ?? '');

$ip_address = $_SERVER['REMOTE_ADDR'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL ? MINUTE)");
$stmt->execute([$ip_address, LOGIN_ATTEMPT_WINDOW_MINUTES]);
$attempts = $stmt->fetchColumn();

if ($attempts >= LOGIN_ATTEMPTS_LIMIT) {
    header('Location: ../login.php?error=too_many_attempts');
    exit;
}

// 3. Input Validation
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

function redirect_with_error($error_code, $pdo, $ip) {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())");
    $stmt->execute([$ip]);
    header('Location: ../login.php?error=' . $error_code);
    exit;
}

if (!$email || empty($password)) {
    redirect_with_error('invalid_input', $pdo, $ip_address);
}

// 4. Business Logic
try {
    $stmt = $pdo->prepare("SELECT id, password, is_verified, account_level FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct
        $delete_stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $delete_stmt->execute([$ip_address]);

        if ($user['is_verified'] == 0) redirect_with_error('not_verified', $pdo, $ip_address);
        if ($user['account_level'] === 'pending_approval') redirect_with_error('pending_approval', $pdo, $ip_address);
        if ($user['account_level'] === 'banned') redirect_with_error('banned', $pdo, $ip_address);
        
        $update_login_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update_login_stmt->execute([$user['id']]);
        
        regenerate_session();
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['account_level'] = $user['account_level'];
        
        header("Location: ../index.php");
        exit;
    } else {
        redirect_with_error('auth_failed', $pdo, $ip_address);
    }
} catch (PDOException $e) {
    error_log("Login DB Error: " . $e->getMessage()); 
    redirect_with_error('server_error', $pdo, $ip_address);
}
?>
