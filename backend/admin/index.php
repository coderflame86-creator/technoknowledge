<?php
/**
 * Technoknowledge Backend - Admin Dashboard Overview
 */

declare(strict_types=1);

$pageTitle = 'Overview Dashboard';
require_once dirname(__DIR__) . '/includes/validator.php';
require_once __DIR__ . '/includes/header.php';

try {
    $db = Database::getConnection();

    // Overall metrics
    $totalCount = (int)$db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
    $newCount   = (int)$db->query("SELECT COUNT(*) FROM submissions WHERE status = 'New'")->fetchColumn();
    $campsCount = (int)$db->query("SELECT COUNT(*) FROM submissions WHERE form_type = 'campRegistration'")->fetchColumn();
    $propsCount = (int)$db->query("SELECT COUNT(*) FROM submissions WHERE form_type = 'proposalRequest'")->fetchColumn();
    $aitepCount = (int)$db->query("SELECT COUNT(*) FROM submissions WHERE form_type = 'aitepStarter'")->fetchColumn();
    $contCount  = (int)$db->query("SELECT COUNT(*) FROM submissions WHERE form_type = 'contact'")->fetchColumn();

    // Status breakdown
    $statusStats = $db->query("SELECT status, COUNT(*) as cnt FROM submissions GROUP BY status")->fetchAll();
    $statusCounts = [];
    foreach ($statusStats as $s) {
        $statusCounts[$s['status']] = (int)$s['cnt'];
    }

    // Latest 10 leads
    $stmt = $db->query("SELECT * FROM submissions ORDER BY id DESC LIMIT 10");
    $recentLeads = $stmt->fetchAll();

} catch (Throwable $e) {
    echo '<div class="alert alert-error">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>

<div class="page-head">
    <div>
        <h1 class="page-title">Website Leads Overview</h1>
        <p class="page-subtitle">Real-time inquiries and registrations collected from the Technoknowledge website.</p>
    </div>
    <div class="page-actions">
        <a href="export.php" class="btn btn-outline">
            📥 Export All (CSV)
        </a>
        <a href="submissions.php" class="btn btn-primary">
            📋 View All Submissions
        </a>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="stats-grid">
    <div class="stat-card stat-card--new">
        <div class="stat-card__label">Pending Action</div>
        <div class="stat-card__value"><?= $newCount ?></div>
        <div class="stat-card__desc">New uncontacted leads</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Total Leads</div>
        <div class="stat-card__value"><?= $totalCount ?></div>
        <div class="stat-card__desc">All-time submissions</div>
    </div>
    <div class="stat-card stat-card--camps">
        <div class="stat-card__label">Techno Camps</div>
        <div class="stat-card__value"><?= $campsCount ?></div>
        <div class="stat-card__desc">Student camp enrollments</div>
    </div>
    <div class="stat-card stat-card--proposals">
        <div class="stat-card__label">School Proposals</div>
        <div class="stat-card__value"><?= $propsCount ?></div>
        <div class="stat-card__desc">ITEC, LMS & Robotics inquiries</div>
    </div>
    <div class="stat-card stat-card--aitep">
        <div class="stat-card__label">AITEP Starter</div>
        <div class="stat-card__value"><?= $aitepCount ?></div>
        <div class="stat-card__desc">AI teacher training sessions</div>
    </div>
    <div class="stat-card stat-card--contact">
        <div class="stat-card__label">Contact Messages</div>
        <div class="stat-card__value"><?= $contCount ?></div>
        <div class="stat-card__desc">Direct visitor inquiries</div>
    </div>
</div>

<!-- Status Distribution Summary -->
<div class="card">
    <div class="card-head">
        <h2 class="card-title">Lead Pipeline & Follow-up Status</h2>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <?php
            $statuses = ['New', 'Contacted', 'Demo / meeting booked', 'Proposal sent', 'Enrolled / signed', 'Not interested'];
            foreach ($statuses as $st):
                $count = $statusCounts[$st] ?? 0;
            ?>
                <a href="submissions.php?status=<?= urlencode($st) ?>" style="flex: 1; min-width: 160px; background: #f8fafc; border: 1px solid var(--tk-border); padding: 14px 18px; border-radius: var(--tk-radius); text-decoration: none;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--tk-text-muted);"><?= htmlspecialchars($st) ?></div>
                    <div style="font-size: 22px; font-weight: 700; color: var(--tk-navy); margin-top: 4px;"><?= $count ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Submissions Table -->
<div class="card">
    <div class="card-head">
        <h2 class="card-title">Recent Inquiries (Latest 10)</h2>
        <a href="submissions.php" class="btn btn-sm btn-outline">View Complete List &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date (PKT)</th>
                    <th>Form Type</th>
                    <th>Name / Organization</th>
                    <th>WhatsApp</th>
                    <th>City</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentLeads)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 36px; color: var(--tk-text-muted);">
                            No submissions recorded yet. Once visitors submit forms on the site, they will appear here automatically.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentLeads as $lead): ?>
                        <?php
                            $wa = FormValidator::formatWhatsApp($lead['whatsapp'] ?? '');
                            $subJson = htmlspecialchars(json_encode($lead, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr data-id="<?= (int)$lead['id'] ?>">
                            <td style="font-weight: 700; color: var(--tk-text-muted);">#<?= (int)$lead['id'] ?></td>
                            <td style="white-space: nowrap; font-size: 13px;">
                                <?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($lead['form_type']) ?>">
                                    <?= htmlspecialchars($lead['form_label']) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($lead['name'] ?: 'Anonymous') ?></strong>
                                <?php if (!empty($lead['institution'])): ?>
                                    <div style="font-size: 12px; color: var(--tk-text-muted);"><?= htmlspecialchars($lead['institution']) ?></div>
                                <?php elseif (!empty($lead['child_name'])): ?>
                                    <div style="font-size: 12px; color: var(--tk-text-muted);">Child: <?= htmlspecialchars($lead['child_name']) ?> (<?= htmlspecialchars($lead['child_age']) ?> yrs)</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($lead['whatsapp'])): ?>
                                    <a href="<?= htmlspecialchars($wa['wa_url']) ?>" target="_blank" class="btn btn-sm btn-whatsapp" title="Chat on WhatsApp" style="padding: 4px 10px; font-size: 12px;">
                                        💬 <?= htmlspecialchars($lead['whatsapp']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--tk-text-muted);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($lead['city'] ?: '&mdash;') ?></td>
                            <td>
                                <span class="badge badge-status badge-status-<?= htmlspecialchars($lead['status']) ?>">
                                    <?= htmlspecialchars($lead['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button type="button" class="btn btn-sm btn-primary" onclick="openLeadModal(<?= $subJson ?>)">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
