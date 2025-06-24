<?php
require_once __DIR__ . '/../config.php';

ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', 1);
}

session_start();

function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($submitted_token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted_token)) {
        http_response_code(403);
        die('CSRF validation failed.');
    }
}
?>
