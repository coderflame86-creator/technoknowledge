<?php
/**
 * Technoknowledge Backend - Form Validator & Anti-Spam Guard
 * Conforms to docs/api-contract.md and frontend/js/components/forms.js
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class FormValidator {
    public const FORMS = [
        'campRegistration' => [
            'label'    => 'Camp Registrations',
            'required' => ['parentName', 'whatsapp', 'childName', 'childAge'],
            'title'    => 'Techno Camps Registration'
        ],
        'proposalRequest' => [
            'label'    => 'Proposal Requests',
            'required' => ['name', 'whatsapp', 'institution'],
            'title'    => 'School Proposal Request'
        ],
        'aitepStarter' => [
            'label'    => 'AITEP Starter Bookings',
            'required' => ['name', 'whatsapp', 'institution'],
            'title'    => 'AITEP Starter Booking'
        ],
        'contact' => [
            'label'    => 'Contact Messages',
            'required' => ['name', 'whatsapp', 'message'],
            'title'    => 'Contact Message'
        ]
    ];

    /**
     * Sanitize string value
     */
    public static function clean(?string $val, int $maxLength = 3000): string {
        if ($val === null) {
            return '';
        }
        $val = trim($val);
        // Strip potential malicious HTML tags
        $val = strip_tags($val);
        if (mb_strlen($val) > $maxLength) {
            $val = mb_substr($val, 0, $maxLength);
        }
        return $val;
    }

    /**
     * Clean and format phone / WhatsApp number for display and wa.me link
     */
    public static function formatWhatsApp(string $phone): array {
        $digits = preg_replace('/\D+/', '', $phone);
        // Convert local Pakistani 03XX... to 923XX...
        if (str_starts_with($digits, '03') && strlen($digits) === 11) {
            $digits = '92' . substr($digits, 1);
        } elseif (str_starts_with($digits, '3') && strlen($digits) === 10) {
            $digits = '92' . $digits;
        }

        return [
            'original' => $phone,
            'clean'    => $digits,
            'wa_url'   => $digits ? ('https://wa.me/' . $digits) : ''
        ];
    }

    /**
     * Check anti-spam and honeypot
     */
    public static function checkSpam(array $data, string $ip, PDO $db, array $config): ?string {
        // 1. Honeypot check (hidden fields in form: hp or hp-field)
        if (!empty($data['hp']) || !empty($data['hp-field'])) {
            return 'Spam detected (honeypot triggered).';
        }

        // 2. Elapsed time check (bots submit in < 2500ms)
        $minFillMs = $config['security']['min_fill_ms'] ?? 2500;
        if (isset($data['elapsed'])) {
            $elapsed = (int)$data['elapsed'];
            // If elapsed is non-zero and under threshold, flag bot
            if ($elapsed > 0 && $elapsed < $minFillMs) {
                return 'Please take a moment to review before submitting.';
            }
        }

        // 3. Rate limiting by IP and by WhatsApp
        $window = $config['security']['rate_limit_window'] ?? 600;
        $maxCount = $config['security']['rate_limit_count'] ?? 5;
        $since = date('Y-m-d H:i:s', time() - $window);

        // Check by IP
        $stmt = $db->prepare("SELECT COUNT(*) FROM rate_limits WHERE identifier = ? AND created_at >= ?");
        $stmt->execute([$ip, $since]);
        if ((int)$stmt->fetchColumn() >= $maxCount) {
            return 'Too many submissions. Please wait a few minutes before trying again or message us on WhatsApp.';
        }

        // Check by phone number if supplied
        $phone = self::clean($data['whatsapp'] ?? '');
        if ($phone !== '') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM rate_limits WHERE identifier = ? AND created_at >= ?");
            $stmt->execute([$phone, $since]);
            if ((int)$stmt->fetchColumn() >= $maxCount) {
                return 'Too many submissions for this phone number. Please contact us directly on WhatsApp.';
            }
        }

        return null;
    }

    /**
     * Check rapid request rate limit (e.g. max 10 requests per minute)
     */
    public static function checkIpRateLimit(string $ip, PDO $db, array $config): array {
        $window = (int)($config['security']['api_rate_limit_window'] ?? 60);
        $maxCount = (int)($config['security']['api_rate_limit_count'] ?? 10);
        $since = date('Y-m-d H:i:s', time() - $window);

        $stmt = $db->prepare("SELECT COUNT(*) FROM rate_limits WHERE identifier = ? AND action = 'submit_attempt' AND created_at >= ?");
        $stmt->execute([$ip, $since]);
        $count = (int)$stmt->fetchColumn();

        if ($count >= $maxCount) {
            return [
                'allowed'   => false,
                'limit'     => $maxCount,
                'remaining' => 0,
                'retry'     => $window,
                'message'   => 'Too many requests. Please wait a minute before trying again.'
            ];
        }

        return [
            'allowed'   => true,
            'limit'     => $maxCount,
            'remaining' => max(0, $maxCount - $count - 1),
            'retry'     => 0,
            'message'   => ''
        ];
    }

    /**
     * Log submit attempt for rate limiting
     */
    public static function logAttempt(string $ip, PDO $db): void {
        if ($ip === '') return;
        $stmt = $db->prepare("INSERT INTO rate_limits (identifier, action, created_at) VALUES (?, 'submit_attempt', ?)");
        $stmt->execute([$ip, date('Y-m-d H:i:s')]);
    }

    /**
     * Record rate limit identifier
     */
    public static function logRateLimit(string $identifier, PDO $db): void {
        if ($identifier === '') return;
        $stmt = $db->prepare("INSERT INTO rate_limits (identifier, action, created_at) VALUES (?, 'submit_form', ?)");
        $stmt->execute([$identifier, date('Y-m-d H:i:s')]);

        // Periodic cleanup: purge rate limits older than 24 hours (approx. 5% of writes)
        if (random_int(1, 20) === 1) {
            try {
                $cutoff = date('Y-m-d H:i:s', time() - 86400);
                $purge = $db->prepare("DELETE FROM rate_limits WHERE created_at < ?");
                $purge->execute([$cutoff]);
            } catch (Throwable $e) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Validate incoming form payload
     */
    public static function validate(string $formKey, array $data): array {
        if (!isset(self::FORMS[$formKey])) {
            return [
                'valid'   => false,
                'message' => 'Unknown form type: ' . htmlspecialchars($formKey)
            ];
        }

        $formDef = self::FORMS[$formKey];
        $missing = [];

        foreach ($formDef['required'] as $field) {
            $val = trim((string)($data[$field] ?? ''));
            if ($val === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            return [
                'valid'   => false,
                'message' => 'Please fill in all required fields: ' . implode(', ', $missing)
            ];
        }

        return [
            'valid' => true,
            'def'   => $formDef
        ];
    }
}
