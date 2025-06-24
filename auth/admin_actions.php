<?php
require_once 'session_config.php';
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['account_level'] !== 'admin') {
    echo json_encode(['error' => 'Access Denied.', 'action' => 'redirect']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_users':
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $status_filter = $_GET['status'] ?? 'all';
            $search_term = $_GET['search'] ?? '';

            $where_clauses = [];
            $params = [];

            if (!empty($search_term)) {
                $where_clauses[] = "email LIKE ?";
                $params[] = "%" . $search_term . "%";
            }
            
            $allowed_statuses = ['pending_approval', 'pending_upgrade', 'free_user', 'paid_user', 'banned', 'admin'];
            if (in_array($status_filter, $allowed_statuses)) {
                $where_clauses[] = "account_level = ?";
                $params[] = $status_filter;
            }

            $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';

            $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM users $where_sql");
            $total_stmt->execute($params);
            $total_users = $total_stmt->fetchColumn();
            $total_pages = ceil($total_users / $limit);

            $users_stmt = $pdo->prepare("SELECT id, email, account_level, reg_date, last_login FROM users $where_sql ORDER BY reg_date DESC LIMIT ? OFFSET ?");
            $users_stmt->execute(array_merge($params, [$limit, $offset]));
            $users = $users_stmt->fetchAll();

            echo json_encode([
                'users' => $users,
                'pagination' => [ 'currentPage' => $page, 'totalPages' => $total_pages, 'totalUsers' => $total_users ]
            ]);
            break;

        case 'update_level':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method.');
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $new_level = $_POST['new_level'] ?? '';
            $allowed_levels = ['free_user', 'paid_user', 'banned', 'admin'];
            if (!$user_id || !in_array($new_level, $allowed_levels)) {
                throw new Exception('Invalid user ID or account level specified.');
            }
            $stmt = $pdo->prepare("UPDATE users SET account_level = ? WHERE id = ?");
            $stmt->execute([$new_level, $user_id]);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
