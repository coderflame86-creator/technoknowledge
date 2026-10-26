<?php
/**
 * Technoknowledge Backend - Configuration
 * Single source of truth for backend settings and environment loading.
 */

declare(strict_types=1);

// Default Timezone
date_default_timezone_set('Asia/Karachi');

// Environment file loader
if (!function_exists('loadEnv')) {
    function loadEnv(string $path): array {
        if (!file_exists($path)) {
            return [];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $val = trim($parts[1]);
                $val = trim($val, '"\'');
                $env[$key] = $val;
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $val;
                }
            }
        }
        return $env;
    }
}

// Load .env if present
$envFile = dirname(__DIR__) . '/.env';
$loadedEnv = loadEnv($envFile);

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        $val = getenv($key);
        return ($val !== false) ? $val : $default;
    }
}

// Configuration Array
return [
    'app_name' => 'Technoknowledge Backend',
    'app_env'  => env('APP_ENV', 'development'),
    'app_url'  => env('APP_URL', 'http://localhost/TK'),

    // Database: 'sqlite' or 'mysql'
    'db' => [
        'driver'   => env('DB_DRIVER', 'sqlite'), // 'sqlite' or 'mysql'
        'sqlite'   => [
            'path' => dirname(__DIR__) . '/data/technoknowledge.sqlite',
        ],
        'mysql'    => [
            'host'    => env('DB_HOST', '127.0.0.1'),
            'port'    => env('DB_PORT', '3306'),
            'name'    => env('DB_NAME', 'technoknowledge'),
            'user'    => env('DB_USER', 'root'),
            'pass'    => env('DB_PASS', ''),
            'charset' => 'utf8mb4',
        ],
    ],

    // Security & Spam Prevention
    'security' => [
        'allowed_origins'   => array_filter(array_unique(array_merge([
            'http://localhost',
            'http://127.0.0.1',
            'https://thetechnoknowledge.com',
            'http://thetechnoknowledge.com',
            'https://www.thetechnoknowledge.com',
            'http://www.thetechnoknowledge.com',
        ], env('ALLOWED_ORIGINS') ? array_map('trim', explode(',', env('ALLOWED_ORIGINS'))) : []))),
        'rate_limit_count'      => (int)env('RATE_LIMIT_COUNT', 5),      // max 5 submissions
        'rate_limit_window'     => (int)env('RATE_LIMIT_WINDOW', 600),   // within 10 minutes (600s)
        'api_rate_limit_count'  => (int)env('API_RATE_LIMIT_COUNT', 10), // max 10 submit requests per minute
        'api_rate_limit_window' => (int)env('API_RATE_LIMIT_WINDOW', 60),// 60s window
        'min_fill_ms'           => (int)env('MIN_FILL_MS', 2500),        // < 2.5s = bot
        'admin_allowed_ips'     => env('ADMIN_ALLOWED_IPS', ''),         // Comma-separated allowed IPs for /backend/admin/
        'session_lifetime'      => 86400 * 7,                            // 7 days
    ],

    // Notification Email & WhatsApp
    'mail' => [
        'notify_email'  => env('NOTIFY_EMAIL', 'technoknowledge19@gmail.com'),
        'from_email'    => env('MAIL_FROM', 'website@thetechnoknowledge.com'),
        'from_name'     => 'Technoknowledge Website Forms',
        'smtp_enabled'  => env('SMTP_HOST') ? true : false,
        'smtp_host'     => env('SMTP_HOST', ''),
        'smtp_port'     => (int)env('SMTP_PORT', 587),
        'smtp_user'     => env('SMTP_USER', ''),
        'smtp_pass'     => env('SMTP_PASS', ''),
    ],

    'company' => [
        'name'           => 'Technoknowledge (SMC-Pvt) Ltd.',
        'tagline'        => 'Creating Technology Producers',
        'whatsapp'       => '923458205013', // +92 345 8205013
        'whatsapp_clean' => '923458205013',
        'phone'          => '+92 322 2055185',
        'email'          => 'info@thetechnoknowledge.com',
    ],

    'statuses' => [
        'New',
        'Contacted',
        'Demo / meeting booked',
        'Proposal sent',
        'Enrolled / signed',
        'Not interested'
    ],
];
