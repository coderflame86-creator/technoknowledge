<?php
/**
 * Technoknowledge Backend - Admin Login
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

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

require_once dirname(__DIR__) . '/config/database.php';

$error = '';
$notice = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'expired') {
    $notice = 'Your session has expired due to inactivity. Please log in again.';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $notice = 'You have logged out successfully.';
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Client IP for brute force defense
$clientIp = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '127.0.0.1';
if (str_contains($clientIp, ',')) {
    $clientIp = trim(explode(',', $clientIp)[0]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        try {
            $db = Database::getConnection();
            $since = date('Y-m-d H:i:s', time() - 900);

            // DB-backed brute-force check: check failed attempts for this IP and username
            $ipCheck = $db->prepare("SELECT COUNT(*) FROM rate_limits WHERE identifier = ? AND action = 'admin_login_failed' AND created_at >= ?");
            $ipCheck->execute([$clientIp, $since]);
            $failedAttempts = (int)$ipCheck->fetchColumn();

            // Session check fallback
            $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
            $_SESSION['last_attempt_time'] = $_SESSION['last_attempt_time'] ?? time();

            if ($failedAttempts >= 5 || ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt_time'] < 900))) {
                $error = "Too many failed login attempts. Please wait 15 minutes before trying again.";
            } elseif (empty($username) || empty($password)) {
                $error = 'Please enter both username and password.';
            } else {
                $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    // Reset attempts on success
                    $_SESSION['login_attempts'] = 0;
                    session_regenerate_id(true);

                    // Clear IP rate limit records
                    $clr = $db->prepare("DELETE FROM rate_limits WHERE identifier = ? AND action = 'admin_login_failed'");
                    $clr->execute([$clientIp]);

                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = (int)$admin['id'];
                    $_SESSION['admin_user'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_last_activity'] = time();

                    // Update last login
                    $up = $db->prepare("UPDATE admins SET last_login = ? WHERE id = ?");
                    $up->execute([date('Y-m-d H:i:s'), $admin['id']]);

                    // Safe Open Redirect validation
                    $redirect = 'index.php';
                    if (!empty($_GET['redirect'])) {
                        $candidate = urldecode($_GET['redirect']);
                        // Disallow external schemes, protocol-relative '//', backslashes, or directory traversal
                        if (!preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*://#', $candidate)
                            && !str_starts_with($candidate, '//')
                            && !str_contains($candidate, '\\')
                            && !str_contains($candidate, '..')) {
                            // Only allow relative internal paths
                            if (preg_match('#^[a-zA-Z0-9_\-/\.\?=&]+$#', $candidate)) {
                                $redirect = $candidate;
                            }
                        }
                    }
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    // Log failed attempt in DB
                    $logFail = $db->prepare("INSERT INTO rate_limits (identifier, action, created_at) VALUES (?, 'admin_login_failed', ?)");
                    $logFail->execute([$clientIp, date('Y-m-d H:i:s')]);
                    $error = 'Invalid username or password.';
                }
            }
        } catch (Throwable $e) {
            error_log('Login DB error: ' . $e->getMessage());
            $error = 'A database error occurred. Please check server configuration.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login • Technoknowledge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body.login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0b1a30 0%, #13294B 60%, #1e3a67 100%);
            padding: 24px;
            margin: 0;
            font-family: 'Nunito Sans', -apple-system, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            padding: 40px 36px;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #FFC21A 0%, #2E8B57 50%, #13294B 100%);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .login-logo img {
            max-width: 160px;
            height: auto;
        }
        .brand-badge {
            display: inline-block;
            background: rgba(19, 41, 75, 0.08);
            color: #13294B;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .login-title {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            color: #13294B;
            margin: 0 0 6px 0;
            text-align: center;
            font-weight: 700;
        }
        .login-sub {
            color: #64748b;
            font-size: 14px;
            text-align: center;
            margin: 0 0 28px 0;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-notice {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus {
            outline: none;
            border-color: #13294B;
            box-shadow: 0 0 0 3px rgba(19, 41, 75, 0.15);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: #13294B;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Sora', sans-serif;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 8px;
        }
        .btn-login:hover {
            background-color: #1d3d6e;
        }
        .btn-login:active {
            transform: scale(0.99);
        }
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .demo-hint {
            margin-top: 20px;
            padding: 12px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            font-size: 12px;
            color: #475569;
            text-align: center;
        }
        .demo-hint strong {
            color: #13294B;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <span class="brand-badge">Administrative Access</span>
            <h1 class="login-title">Technoknowledge</h1>
            <p class="login-sub">Sign in to manage website leads & forms</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($notice)): ?>
            <div class="alert alert-notice">
                <?= htmlspecialchars($notice) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="admin" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required>
            </div>

            <button type="submit" class="btn-login">Sign In to Dashboard</button>
        </form>

        <?php
        $config = require dirname(__DIR__) . '/config/config.php';
        if (($config['app_env'] ?? 'development') === 'development'):
        ?>
        <div class="demo-hint">
            <strong>Development Environment Hint:</strong><br>
            Username: <code>admin</code> &bull; Password: <code>TechnoAdmin2026!</code><br>
            <span style="color:#64748b;">(Hidden in production. Remember to change password in Settings.)</span>
        </div>
        <?php endif; ?>

        <div class="login-footer">
            Technoknowledge (SMC-Pvt) Ltd. &bull; Internal Portal
        </div>
    </div>
</body>
</html>
