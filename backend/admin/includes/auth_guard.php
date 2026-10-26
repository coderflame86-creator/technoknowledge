<?php
/**
 * Technoknowledge Backend - Admin Authentication Guard
 */

declare(strict_types=1);

$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure'   => $isHttps,
        'use_strict_mode' => true,
    ]);
}

// Security & SEO headers (strictly block indexing across all admin pages)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);

// Verify IP access limits if configured in .env
if (!function_exists('check_admin_ip_access')) {
    function check_admin_ip_access(): void {
        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $rawAllowed = trim((string)($config['security']['admin_allowed_ips'] ?? ''));
        if ($rawAllowed === '') {
            return; // No IP restriction configured
        }

        $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '127.0.0.1';
        if (str_contains($clientIp, ',')) {
            $clientIp = trim(explode(',', $clientIp)[0]);
        }

        $allowedIps = array_filter(array_map('trim', explode(',', $rawAllowed)));
        $allowedIps[] = '127.0.0.1';
        $allowedIps[] = '::1';

        if (!in_array($clientIp, $allowedIps, true)) {
            http_response_code(403);
            header('Content-Type: text/html; charset=utf-8');
            header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="robots" content="noindex, nofollow"><title>403 Access Restricted</title><style>body{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;text-align:center;padding:60px 20px;background:#f8f9fa;color:#13294B;}h1{font-size:28px;color:#c92a2a;margin-bottom:12px;}p{font-size:16px;color:#495057;}</style></head><body><h1>403 Access Restricted</h1><p>Your IP address (' . htmlspecialchars($clientIp) . ') is not authorized to access this administration portal.</p></body></html>';
            exit;
        }
    }
}
check_admin_ip_access();

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
