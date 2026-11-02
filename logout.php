<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Logout / delete must use POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Verify request token
verify_csrf();

$action = $_POST['action'] ?? 'logout';
$status = 'logged_out';

/* ---------- Delete the account from the database ---------- */
if ($action === 'delete') {
    $userId  = (int)($_SESSION['user_id'] ?? 0);
    $confirm = trim($_POST['confirm_text'] ?? '');

    // Must be logged in and must have typed DELETE
    if ($userId <= 0 || $confirm !== 'DELETE') {
        header('Location: dashboard.php?delete_failed=1');
        exit;
    }

    try {
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $status = 'deleted';
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        header('Location: dashboard.php?delete_failed=1');
        exit;
    }
}

/* ---------- End the session ---------- */
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: index.php?status=' . $status);
exit;