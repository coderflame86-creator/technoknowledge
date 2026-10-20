<?php
/**
 * Technoknowledge Backend - Submissions & Leads Management
 */

declare(strict_types=1);

$pageTitle = 'Leads & Submissions Management';
require_once dirname(__DIR__) . '/includes/validator.php';
require_once __DIR__ . '/includes/header.php';

try {
    $db = Database::getConnection();

    // Query parameters
    $selectedForm   = trim((string)($_GET['form'] ?? ''));
    $selectedStatus = trim((string)($_GET['status'] ?? ''));
    $searchQuery    = trim((string)($_GET['q'] ?? ''));
    $page           = max(1, (int)($_GET['page'] ?? 1));
    $perPage        = 20;
    $offset         = ($page - 1) * $perPage;

    // Build filter query
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

    // Total filtered records
    $countStmt = $db->prepare("SELECT COUNT(*) FROM submissions $whereSql");
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalRecords / $perPage));

    // Fetch page records
    $selectSql = "SELECT * FROM submissions $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";
    $stmt = $db->prepare($selectSql);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll();

} catch (Throwable $e) {
    echo '<div class="alert alert-error">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Build export URL with current filters
$exportQuery = http_build_query([
    'form'   => $selectedForm,
    'status' => $selectedStatus,
    'q'      => $searchQuery
]);
?>

<div class="page-head">
    <div>
        <h1 class="page-title">Website Form Submissions</h1>
        <p class="page-subtitle">Filter, search, manage status and contact leads directly via WhatsApp.</p>
    </div>
    <div class="page-actions">
        <a href="export.php?<?= htmlspecialchars($exportQuery) ?>" class="btn btn-accent">
            📥 Export Filtered (CSV)
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="submissions.php" class="filter-bar" style="margin-bottom: 0;">
            <input type="text" name="q" class="form-input search-input" placeholder="🔍 Search name, phone, school, email, city..." value="<?= htmlspecialchars($searchQuery) ?>">

            <select name="form" class="form-select">
                <option value="">All Form Types</option>
                <option value="campRegistration" <?= ($selectedForm === 'campRegistration') ? 'selected' : '' ?>>Techno Camps Registration</option>
                <option value="proposalRequest" <?= ($selectedForm === 'proposalRequest') ? 'selected' : '' ?>>School Proposal Requests</option>
                <option value="aitepStarter" <?= ($selectedForm === 'aitepStarter') ? 'selected' : '' ?>>AITEP Starter Bookings</option>
                <option value="contact" <?= ($selectedForm === 'contact') ? 'selected' : '' ?>>Contact Messages</option>
            </select>

            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <?php foreach (['New', 'Contacted', 'Demo / meeting booked', 'Proposal sent', 'Enrolled / signed', 'Not interested'] as $st): ?>
                    <option value="<?= htmlspecialchars($st) ?>" <?= ($selectedStatus === $st) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if ($selectedForm !== '' || $selectedStatus !== '' || $searchQuery !== ''): ?>
                <a href="submissions.php" class="btn btn-outline btn-sm">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Submissions Table Card -->
<div class="card">
    <div class="card-head">
        <h2 class="card-title">
            Submissions List (<?= number_format($totalRecords) ?> <?= ($totalRecords === 1) ? 'lead' : 'leads' ?>)
        </h2>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date (PKT)</th>
                    <th>Form</th>
                    <th>Contact Info</th>
                    <th>Key Details</th>
                    <th>City</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 48px; color: var(--tk-text-muted);">
                            No submissions found matching your filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($submissions as $sub): ?>
                        <?php
                            $wa = FormValidator::formatWhatsApp($sub['whatsapp'] ?? '');
                            $subJson = htmlspecialchars(json_encode($sub, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr data-id="<?= (int)$sub['id'] ?>">
                            <td style="font-weight: 700; color: var(--tk-text-muted);">#<?= (int)$sub['id'] ?></td>
                            <td style="white-space: nowrap; font-size: 13px;">
                                <?= date('d M Y, h:i A', strtotime($sub['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($sub['form_type']) ?>">
                                    <?= htmlspecialchars($sub['form_label']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--tk-navy);"><?= htmlspecialchars($sub['name'] ?: 'Anonymous') ?></div>
                                <?php if (!empty($sub['whatsapp'])): ?>
                                    <div style="margin-top: 4px;">
                                        <a href="<?= htmlspecialchars($wa['wa_url']) ?>" target="_blank" class="btn btn-sm btn-whatsapp" style="padding: 2px 8px; font-size: 11px;">
                                            💬 <?= htmlspecialchars($sub['whatsapp']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($sub['email'])): ?>
                                    <div style="font-size: 12px; color: var(--tk-text-muted); margin-top: 2px;">
                                        ✉️ <a href="mailto:<?= htmlspecialchars($sub['email']) ?>"><?= htmlspecialchars($sub['email']) ?></a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sub['form_type'] === 'campRegistration'): ?>
                                    <strong>Child:</strong> <?= htmlspecialchars($sub['child_name']) ?> (<?= htmlspecialchars($sub['child_age']) ?> yrs)<br>
                                    <?php if (!empty($sub['courses'])): ?>
                                        <span style="font-size: 12px; color: var(--tk-text-muted);">Courses: <?= htmlspecialchars($sub['courses']) ?></span>
                                    <?php endif; ?>
                                <?php elseif ($sub['form_type'] === 'proposalRequest'): ?>
                                    <strong><?= htmlspecialchars($sub['institution'] ?: 'Institution') ?></strong>
                                    <?php if (!empty($sub['program'])): ?>
                                        <div style="font-size: 12px; color: var(--tk-text-muted);">Program: <?= htmlspecialchars($sub['program']) ?></div>
                                    <?php endif; ?>
                                <?php elseif ($sub['form_type'] === 'aitepStarter'): ?>
                                    <strong><?= htmlspecialchars($sub['institution'] ?: 'AITEP School') ?></strong>
                                    <div style="font-size: 12px; color: var(--tk-text-muted);"><?= htmlspecialchars($sub['interest'] ?: 'Starter Session') ?></div>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($sub['subject'] ?: 'Inquiry') ?></strong>
                                    <div style="font-size: 12px; color: var(--tk-text-muted); max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= htmlspecialchars($sub['message'] ?: '') ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($sub['city'] ?: '&mdash;') ?></td>
                            <td>
                                <span class="badge badge-status badge-status-<?= htmlspecialchars($sub['status']) ?>">
                                    <?= htmlspecialchars($sub['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button type="button" class="btn btn-sm btn-primary" data-lead="<?= $subJson ?>" onclick="openLeadModal(this.getAttribute('data-lead'))" style="margin-right: 4px;">
                                    View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline" onclick="deleteSubmission(<?= (int)$sub['id'] ?>)" style="color: #dc2626; border-color: #fecaca;">
                                    &times;
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="padding: 16px 24px; border-top: 1px solid var(--tk-border); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; color: var(--tk-text-muted);">
                Showing page <?= $page ?> of <?= $totalPages ?>
            </div>
            <div style="display: flex; gap: 6px;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                        $pageParams = array_merge($_GET, ['page' => $i]);
                        $pageUrl = 'submissions.php?' . http_build_query($pageParams);
                    ?>
                    <a href="<?= htmlspecialchars($pageUrl) ?>" class="btn btn-sm <?= ($i === $page) ? 'btn-primary' : 'btn-outline' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
