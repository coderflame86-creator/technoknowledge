<?php
/**
 * Technoknowledge Backend - Admin Internal API Endpoint
 * Handles lead updates, note additions, and deletions.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/auth_guard.php';
require_once dirname(__DIR__) . '/config/database.php';

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?: $_POST;

$action = $payload['action'] ?? '';
$csrfToken = $payload['csrf_token'] ?? '';

if (!verify_csrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'CSRF verification failed.']);
    exit;
}

try {
    $db = Database::getConnection();

    if ($action === 'update_lead') {
        $id = (int)($payload['id'] ?? 0);
        $status = trim((string)($payload['status'] ?? 'New'));
        $notes = trim((string)($payload['notes'] ?? ''));

        if ($id <= 0) {
            echo json_encode(['ok' => false, 'message' => 'Invalid submission ID.']);
            exit;
        }

        $config = require dirname(__DIR__) . '/config/config.php';
        $allowedStatuses = $config['statuses'] ?? [
            'New', 'Contacted', 'Demo / meeting booked', 'Proposal sent', 'Enrolled / signed', 'Not interested'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            echo json_encode(['ok' => false, 'message' => 'Invalid status selected.']);
            exit;
        }

        $stmt = $db->prepare("UPDATE submissions SET status = ?, admin_notes = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$status, $notes, date('Y-m-d H:i:s'), $id]);

        echo json_encode(['ok' => true, 'message' => 'Lead updated successfully.']);
        exit;
    }

    if ($action === 'delete_submission') {
        $id = (int)($payload['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'message' => 'Invalid submission ID.']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM submissions WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['ok' => true, 'message' => 'Submission deleted.']);
        exit;
    }

    echo json_encode(['ok' => false, 'message' => 'Unknown action.']);
} catch (Throwable $e) {
    error_log('Admin API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
