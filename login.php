<?php
require_once __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
verify_csrf();
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    flash_error('Enter your email and password.'); header('Location: index.php'); exit;
}
try {
    $stmt = db()->prepare('SELECT id, username, email, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    flash_error('Database is not ready yet. Follow the README setup steps.');
    header('Location: index.php'); exit;
}
if (!$user || !password_verify($password, $user['password_hash'])) {
    flash_error('Email or password is incorrect.'); header('Location: index.php'); exit;
}
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
header('Location: dashboard.php'); exit;
