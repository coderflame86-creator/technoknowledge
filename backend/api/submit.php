<?php
/**
 * Technoknowledge Backend - Form Submission Endpoint
 * Receives POST data from frontend forms, validates, blocks spam,
 * saves to database, and triggers notification.
 */

declare(strict_types=1);

// Security & JSON headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/validator.php';
require_once dirname(__DIR__) . '/includes/mailer.php';

$config = require dirname(__DIR__) . '/config/config.php';

// Handle CORS safely
$rawOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = $config['security']['allowed_origins'] ?? [];

if (!empty($rawOrigin)) {
    $parsedOrigin = rtrim($rawOrigin, '/');
    $isAllowed = false;
    foreach ($allowedOrigins as $allowed) {
        if (rtrim($allowed, '/') === $parsedOrigin) {
            $isAllowed = true;
            break;
        }
    }
    if ($isAllowed) {
        header("Access-Control-Allow-Origin: $parsedOrigin");
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Vary: Origin');
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Origin not allowed by CORS policy.']);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed. Only POST is accepted.']);
    exit;
}

try {
    $db = Database::getConnection();
} catch (Throwable $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error. Database unavailable.']);
    exit;
}

// Parse request payload
$rawInput = file_get_contents('php://input');
$data = [];

if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

if (empty($data) && !empty($_POST)) {
    $data = $_POST;
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'No submission data provided.']);
    exit;
}

// Client IP & User Agent
$ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '127.0.0.1';
// Handle multiple IPs in X-Forwarded-For
if (str_contains($ip, ',')) {
    $ip = trim(explode(',', $ip)[0]);
}
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Check anti-spam and honeypot
$spamError = FormValidator::checkSpam($data, $ip, $db, $config);
if ($spamError !== null) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'message' => $spamError]);
    exit;
}

// Identify form key
$formInput = (string)($data['form'] ?? '');
$formKey = '';

// Map by key or label
$labelMap = [
    'Camp Registrations'     => 'campRegistration',
    'campRegistration'       => 'campRegistration',
    'Proposal Requests'      => 'proposalRequest',
    'proposalRequest'        => 'proposalRequest',
    'AITEP Starter Bookings' => 'aitepStarter',
    'aitepStarter'           => 'aitepStarter',
    'Contact Messages'       => 'contact',
    'contact'                => 'contact'
];

if (isset($labelMap[$formInput])) {
    $formKey = $labelMap[$formInput];
} else {
    // Check if form matches any known keys
    foreach (FormValidator::FORMS as $key => $def) {
        if (strcasecmp($key, $formInput) === 0 || strcasecmp($def['label'], $formInput) === 0) {
            $formKey = $key;
            break;
        }
    }
}

if ($formKey === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Unknown form: ' . htmlspecialchars($formInput)]);
    exit;
}

// Validate fields
$valResult = FormValidator::validate($formKey, $data);
if (!$valResult['valid']) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $valResult['message']]);
    exit;
}

$formDef = $valResult['def'];

// Helper to sanitize
$clean = function($key) use ($data): string {
    return FormValidator::clean($data[$key] ?? null);
};

// Helper to format arrays (checkboxes/multi-select)
$formatList = function($key) use ($data): string {
    $val = $data[$key] ?? null;
    if (is_array($val)) {
        $items = array_map(function($v) {
            return FormValidator::clean((string)$v);
        }, $val);
        return implode(', ', array_filter($items));
    }
    return FormValidator::clean($val);
};

// Extract and normalize values
$name = $clean('name');
if ($name === '' && !empty($data['parentName'])) {
    $name = $clean('parentName');
}

$submission = [
    'form_type'      => $formKey,
    'form_label'     => $formDef['label'],
    'name'           => $name,
    'whatsapp'       => $clean('whatsapp'),
    'email'          => $clean('email'),
    'institution'    => $clean('institution'),
    'role'           => $clean('role'),
    'city'           => $clean('city'),
    'child_name'     => $clean('childName'),
    'child_age'      => $clean('childAge'),
    'courses'        => $formatList('courses'),
    'class_type'     => $clean('classType'),
    'program'        => $clean('program'),
    'interest'       => $clean('interest'),
    'interests'      => $formatList('interests'),
    'iam'            => $clean('iam'),
    'subject'        => $clean('subject'),
    'message'        => $clean('message'),
    'notes'          => $clean('notes'),
    'teachers_count' => $clean('teachers'),
    'students_count' => $clean('students'),
    'source'         => $clean('source'),
    'page'           => $clean('page') ?: ($_SERVER['HTTP_REFERER'] ?? ''),
    'status'         => 'New',
    'admin_notes'    => '',
    'ip_address'     => $ip,
    'user_agent'     => $userAgent,
    'consent'        => !empty($data['consent']) ? 1 : 0,
    'raw_json'       => json_encode($data, JSON_UNESCAPED_UNICODE),
    'created_at'     => date('Y-m-d H:i:s'),
    'updated_at'     => date('Y-m-d H:i:s'),
];

// Insert into database
$sql = "INSERT INTO submissions (
    form_type, form_label, name, whatsapp, email, institution, role, city,
    child_name, child_age, courses, class_type, program, interest, interests,
    iam, subject, message, notes, teachers_count, students_count, source, page,
    status, admin_notes, ip_address, user_agent, consent, raw_json, created_at, updated_at
) VALUES (
    :form_type, :form_label, :name, :whatsapp, :email, :institution, :role, :city,
    :child_name, :child_age, :courses, :class_type, :program, :interest, :interests,
    :iam, :subject, :message, :notes, :teachers_count, :students_count, :source, :page,
    :status, :admin_notes, :ip_address, :user_agent, :consent, :raw_json, :created_at, :updated_at
)";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':form_type'      => $submission['form_type'],
        ':form_label'     => $submission['form_label'],
        ':name'           => $submission['name'],
        ':whatsapp'       => $submission['whatsapp'],
        ':email'          => $submission['email'],
        ':institution'    => $submission['institution'],
        ':role'           => $submission['role'],
        ':city'           => $submission['city'],
        ':child_name'     => $submission['child_name'],
        ':child_age'      => $submission['child_age'],
        ':courses'        => $submission['courses'],
        ':class_type'     => $submission['class_type'],
        ':program'        => $submission['program'],
        ':interest'       => $submission['interest'],
        ':interests'      => $submission['interests'],
        ':iam'            => $submission['iam'],
        ':subject'        => $submission['subject'],
        ':message'        => $submission['message'],
        ':notes'          => $submission['notes'],
        ':teachers_count' => $submission['teachers_count'],
        ':students_count' => $submission['students_count'],
        ':source'         => $submission['source'],
        ':page'           => $submission['page'],
        ':status'         => $submission['status'],
        ':admin_notes'    => $submission['admin_notes'],
        ':ip_address'     => $submission['ip_address'],
        ':user_agent'     => $submission['user_agent'],
        ':consent'        => $submission['consent'],
        ':raw_json'       => $submission['raw_json'],
        ':created_at'     => $submission['created_at'],
        ':updated_at'     => $submission['updated_at'],
    ]);
    $submissionId = (int)$db->lastInsertId();
    $submission['id'] = $submissionId;
} catch (Throwable $e) {
    error_log('Insert submission failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to save submission. Please try again.']);
    exit;
}

// Log rate limit
FormValidator::logRateLimit($ip, $db);
if (!empty($submission['whatsapp'])) {
    FormValidator::logRateLimit($submission['whatsapp'], $db);
}

// Notify via email (async/safe)
try {
    FormMailer::notify($submission, $config);
} catch (Throwable $e) {
    error_log('Mailer error: ' . $e->getMessage());
}

// Return success matching frontend contract
echo json_encode([
    'ok'      => true,
    'message' => 'Form submitted successfully.',
    'id'      => $submissionId
]);
