<?php
/**
 * Technoknowledge Backend - Admin Authentication Guard
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Check session
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
    header('Location: login.php?redirect=' . $redirect);
    exit;
}

// Inactivity check (2 hours)
$maxInactive = 7200;
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > $maxInactive)) {
    session_unset();
    session_destroy();
    header('Location: login.php?msg=expired');
    exit;
}

$_SESSION['admin_last_activity'] = time();

// CSRF helper
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string {
    return $_SESSION['csrf_token'] ?? '';
}

function verify_csrf(?string $token): bool {
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
