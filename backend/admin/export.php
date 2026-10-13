<?php
/**
 * Technoknowledge Backend - Export Leads to CSV
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth_guard.php';
require_once dirname(__DIR__) . '/config/database.php';

try {
    $db = Database::getConnection();

    $selectedForm   = trim((string)($_GET['form'] ?? ''));
    $selectedStatus = trim((string)($_GET['status'] ?? ''));
    $searchQuery    = trim((string)($_GET['q'] ?? ''));

    $where = [];
    $params = [];

    if ($selectedForm !== '') {
        $where[] = 'form_type = ?';
        $params[] = $selectedForm;
    }

    if ($selectedStatus !== '') {
        $where[] = 'status = ?';
        $params[] = $selectedStatus;
    }

    if ($searchQuery !== '') {
        $where[] = '(name LIKE ? OR whatsapp LIKE ? OR email LIKE ? OR institution LIKE ? OR city LIKE ? OR child_name LIKE ? OR message LIKE ?)';
        $searchTerm = '%' . $searchQuery . '%';
        $params = array_merge($params, array_fill(0, 7, $searchTerm));
    }

    $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
    $stmt = $db->prepare("SELECT * FROM submissions $whereSql ORDER BY id DESC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $filename = 'technoknowledge_leads_' . date('Y-m-d_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output stream
    $out = fopen('php://output', 'w');

    // Write UTF-8 BOM for Microsoft Excel compatibility
    fwrite($out, "\xEF\xBB\xBF");

    // CSV Header Row
    fputcsv($out, [
        'ID',
        'Submission Date (PKT)',
        'Form Label',
        'Form Type',
        'Status',
        'Contact Name',
        'WhatsApp Number',
        'Email Address',
        'Institution / School',
        'Role',
        'City',
        'Child Name',
        'Child Age',
        'Courses',
        'Class Type',
        'Program',
        'Option Chosen',
        'Interests',
        'I Am',
        'Subject',
        'Message',
        'Teachers',
        'Students',
        'Source (Heard From)',
        'Sent From Page',
        'Internal Admin Notes'
    ]);

    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'],
            $r['created_at'],
            $r['form_label'],
            $r['form_type'],
            $r['status'],
            $r['name'],
            $r['whatsapp'],
            $r['email'],
            $r['institution'],
            $r['role'],
            $r['city'],
            $r['child_name'],
            $r['child_age'],
            $r['courses'],
            $r['class_type'],
            $r['program'],
            $r['interest'],
            $r['interests'],
            $r['iam'],
            $r['subject'],
            $r['message'],
            $r['teachers_count'],
            $r['students_count'],
            $r['source'],
            $r['page'],
            $r['admin_notes']
        ]);
    }

    fclose($out);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo "Export error: " . $e->getMessage();
}
