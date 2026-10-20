<?php
/**
 * Technoknowledge Backend - Admin Settings & Diagnostics
 */

declare(strict_types=1);

$pageTitle = 'Settings & Diagnostics';
require_once __DIR__ . '/includes/header.php';

$successMsg = '';
$errorMsg   = '';

try {
    $db = Database::getConnection();
    $adminId = (int)($_SESSION['admin_id'] ?? 1);

    // Fetch current admin
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $currentAdmin = $stmt->fetch();

    // Handle Password / Profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            $errorMsg = 'Security validation failed. Please refresh.';
        } else {
            $name        = trim((string)($_POST['name'] ?? ''));
            $email       = trim((string)($_POST['email'] ?? ''));
            $currentPass = (string)($_POST['current_password'] ?? '');
            $newPass     = (string)($_POST['new_password'] ?? '');
            $confirmPass = (string)($_POST['confirm_password'] ?? '');

            if (empty($name) || empty($email)) {
                $errorMsg = 'Name and email cannot be empty.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = 'Please enter a valid email address.';
            } else {
                // If changing password
                if (!empty($newPass)) {
                    if (empty($currentPass) || !password_verify($currentPass, $currentAdmin['password_hash'])) {
                        $errorMsg = 'Current password entered is incorrect.';
                    } elseif (strlen($newPass) < 8) {
                        $errorMsg = 'New password must be at least 8 characters long.';
                    } elseif ($newPass !== $confirmPass) {
                        $errorMsg = 'New password and confirmation do not match.';
                    } else {
                        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                        $up = $db->prepare("UPDATE admins SET name = ?, email = ?, password_hash = ? WHERE id = ?");
                        $up->execute([$name, $email, $newHash, $adminId]);
                        $_SESSION['admin_name'] = $name;
                        $_SESSION['admin_email'] = $email;
                        $successMsg = 'Profile and password updated successfully!';
                    }
                } else {
                    $up = $db->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
                    $up->execute([$name, $email, $adminId]);
                    $_SESSION['admin_name'] = $name;
                    $_SESSION['admin_email'] = $email;
                    $successMsg = 'Profile updated successfully!';
                }
            }
        }
    }

    // Handle Test Email
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_test_email') {
        if (verify_csrf($_POST['csrf_token'] ?? '')) {
            require_once dirname(__DIR__) . '/includes/mailer.php';
            require_once dirname(__DIR__) . '/includes/validator.php';
            $config = require dirname(__DIR__) . '/config/config.php';

            $dummySubmission = [
                'form_label'     => 'Test Email Notification',
                'name'           => 'Technoknowledge Admin Test',
                'whatsapp'       => $config['company']['whatsapp'],
                'email'          => $config['mail']['notify_email'],
                'institution'    => 'Technoknowledge Test Lab',
                'role'           => 'Administrator',
                'city'           => 'Karachi',
                'child_name'     => null,
                'child_age'      => null,
                'courses'        => null,
                'class_type'     => null,
                'program'        => 'Diagnostic Test',
                'interest'       => 'Test run',
                'interests'      => null,
                'iam'            => 'Admin',
                'subject'        => 'Test Email Alert',
                'message'        => 'This is a test notification confirming email delivery is working properly.',
                'teachers_count' => null,
                'students_count' => null,
                'notes'          => 'Test triggered from Settings page.',
                'page'           => '/backend/admin/settings.php',
                'created_at'     => date('Y-m-d H:i:s')
            ];

            $sent = FormMailer::notify($dummySubmission, $config);
            if ($sent) {
                $successMsg = 'Test email dispatched to ' . htmlspecialchars($config['mail']['notify_email']) . '! Check your inbox.';
            } else {
                $errorMsg = 'Could not dispatch email via standard PHP mail(). If using local XAMPP without a local mail server, configure SMTP in backend/.env.';
            }
        }
    }

    // System stats
    $totalCount = (int)$db->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
    $driver = Database::getDriver();
    $config = require dirname(__DIR__) . '/config/config.php';

} catch (Throwable $e) {
    $errorMsg = 'Error loading settings: ' . $e->getMessage();
}
?>

<div class="page-head">
    <div>
        <h1 class="page-title">Settings & System Info</h1>
        <p class="page-subtitle">Configure administrator access and review backend system diagnostics.</p>
    </div>
</div>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-notice" style="margin-bottom: 24px;">
        ✅ <?= htmlspecialchars($successMsg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="alert alert-error" style="margin-bottom: 24px;">
        ⚠️ <?= htmlspecialchars($errorMsg) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
    <!-- Profile & Security Card -->
    <div class="card">
        <div class="card-head">
            <h2 class="card-title">Admin Account & Password</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($currentAdmin['username'] ?? 'admin') ?>" disabled style="background:#f1f5f9;">
                    <small style="color:var(--tk-text-muted);">Username cannot be changed.</small>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Display Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($currentAdmin['name'] ?? 'Admin') ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label">Admin Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentAdmin['email'] ?? '') ?>" required>
                </div>

                <hr style="border:none; border-top:1px solid var(--tk-border); margin: 24px 0;">

                <h3 style="font-size: 16px; margin-bottom: 14px; color: var(--tk-navy);">Change Password</h3>
                <p style="font-size: 13px; color: var(--tk-text-muted); margin-bottom: 16px;">Leave blank if you do not want to change your password.</p>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Required only if changing password">
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="At least 8 characters">
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-type new password">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- System Diagnostics Card -->
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-head">
                <h2 class="card-title">System Diagnostics</h2>
            </div>
            <div class="card-body">
                <table class="detail-table" style="margin-bottom: 0;">
                    <tbody>
                        <tr>
                            <th>Database Driver</th>
                            <td>
                                <strong style="color:var(--tk-green); text-transform:uppercase;">
                                    <?= htmlspecialchars($driver) ?>
                                </strong>
                                <span style="font-size:12px; color:var(--tk-text-muted);">
                                    (<?= ($driver === 'sqlite') ? 'Zero-setup SQLite active' : 'MySQL Database active' ?>)
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Leads Saved</th>
                            <td><strong><?= number_format($totalCount) ?></strong> submissions</td>
                        </tr>
                        <tr>
                            <th>PHP Version</th>
                            <td>PHP <?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <th>Web Server</th>
                            <td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></td>
                        </tr>
                        <tr>
                            <th>Timezone</th>
                            <td><?= date_default_timezone_get() ?> (Current: <?= date('h:i:s A, d M Y') ?>)</td>
                        </tr>
                        <tr>
                            <th>Form Endpoint</th>
                            <td><code>/backend/api/submit.php</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2 class="card-title">Email Notifications</h2>
            </div>
            <div class="card-body">
                <p style="font-size: 14px; color: var(--tk-text-muted); margin-bottom: 14px;">
                    Every website submission triggers an instant email alert to:
                    <strong style="color:var(--tk-navy);"><?= htmlspecialchars($config['mail']['notify_email']) ?></strong>.
                </p>

                <form method="POST" action="settings.php">
                    <input type="hidden" name="action" value="send_test_email">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <button type="submit" class="btn btn-outline" style="width: 100%;">
                        ✉️ Send Test Alert Email
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
