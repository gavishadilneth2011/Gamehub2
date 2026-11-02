<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path' => '/',
    ]);
    session_start();
}
require_once __DIR__ . '/../config/database.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    try {
        $stmt = db()->prepare('SELECT id, username, email FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user) { unset($_SESSION['user_id']); return null; }
        return $user;
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        return null;
    }
}
function require_login(): array {
    $user = current_user();
    if (!$user) { header('Location: index.php'); exit; }
    return $user;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    if (!is_string($sent) || !is_string($known) || $known === '' || !hash_equals($known, $sent)) {
        http_response_code(403);
        exit('Invalid form token. Please go back and try again.');
    }
}
function flash_error(string $message): void { $_SESSION['flash_error'] = $message; }
