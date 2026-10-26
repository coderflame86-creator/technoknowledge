<?php
/**
 * Technoknowledge Backend - Admin Header Component
 */

declare(strict_types=1);

require_once __DIR__ . '/auth_guard.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentForm = $_GET['form'] ?? '';

// Unread count
try {
    $db = Database::getConnection();
    $unreadCount = (int)$db->query("SELECT COUNT(*) FROM submissions WHERE status = 'New'")->fetchColumn();
} catch (Throwable $e) {
    $unreadCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard') ?> • Technoknowledge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script>
        window.CSRF_TOKEN = '<?= htmlspecialchars(csrf_token()) ?>';
    </script>
</head>
<body>
    <header class="admin-header">
        <div class="admin-header__inner">
            <div class="admin-header__left">
                <button type="button" class="admin-menu-toggle" id="adminMenuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span class="admin-menu-toggle__icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                    <span class="sr-only" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">Menu</span>
                </button>

                <a href="index.php" class="admin-brand">
                    <span>Technoknowledge</span>
                </a>
            </div>

            <!-- Mobile quick actions on right of topbar -->
            <div class="admin-header__mobile-actions">
                <?php if ($unreadCount > 0): ?>
                    <a href="submissions.php?status=New" class="admin-mobile-badge" title="<?= $unreadCount ?> new leads">
                        <?= $unreadCount ?> new
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="admin-mobile-logout" title="Logout">🚪</a>
            </div>

            <!-- Collapsible Navigation (Inline on Desktop, Drawer on Mobile) -->
            <div class="admin-nav-collapse" id="adminNavCollapse">
                <div class="admin-drawer__header">
                    <div class="admin-brand">
                        <span>Technoknowledge</span>
                        <span class="admin-brand__badge">Portal</span>
                    </div>
                    <button type="button" class="admin-drawer__close" id="adminDrawerClose" aria-label="Close menu">&times;</button>
                </div>

                <div class="admin-drawer__user">
                    <div class="admin-drawer__avatar">
                        <?= strtoupper(substr((string)($_SESSION['admin_user'] ?? 'A'), 0, 1)) ?>
                    </div>
                    <div class="admin-drawer__user-meta">
                        <div class="admin-drawer__name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?></div>
                        <div class="admin-drawer__username">@<?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></div>
                    </div>
                </div>

                <nav class="admin-nav">
                    <a href="index.php" class="admin-nav__link <?= ($currentPage === 'index.php') ? 'is-active' : '' ?>">
                        <span class="nav-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="submissions.php" class="admin-nav__link <?= ($currentPage === 'submissions.php' && empty($currentForm)) ? 'is-active' : '' ?>">
                        <span class="nav-icon">📋</span>
                        <span>All Leads</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="admin-nav__badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="submissions.php?form=campRegistration" class="admin-nav__link <?= ($currentForm === 'campRegistration') ? 'is-active' : '' ?>">
                        <span class="nav-icon">⛺</span>
                        <span>Techno Camps</span>
                    </a>
                    <a href="submissions.php?form=proposalRequest" class="admin-nav__link <?= ($currentForm === 'proposalRequest') ? 'is-active' : '' ?>">
                        <span class="nav-icon">🏫</span>
                        <span>School Proposals</span>
                    </a>
                    <a href="submissions.php?form=aitepStarter" class="admin-nav__link <?= ($currentForm === 'aitepStarter') ? 'is-active' : '' ?>">
                        <span class="nav-icon">🤖</span>
                        <span>AITEP Starter</span>
                    </a>
                    <a href="submissions.php?form=contact" class="admin-nav__link <?= ($currentForm === 'contact') ? 'is-active' : '' ?>">
                        <span class="nav-icon">✉️</span>
                        <span>Contact</span>
                    </a>
                    <a href="settings.php" class="admin-nav__link <?= ($currentPage === 'settings.php') ? 'is-active' : '' ?>">
                        <span class="nav-icon">⚙️</span>
                        <span>Settings</span>
                    </a>
                </nav>

                <div class="admin-user-menu">
                    <a href="../../frontend/index.html" target="_blank" class="btn btn-sm btn-outline btn-website">
                        🌐 View Website
                    </a>
                    <div class="admin-user__name">
                        Signed in as <strong><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></strong>
                    </div>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <!-- Backdrop overlay for mobile drawer -->
            <div class="admin-nav-overlay" id="adminNavOverlay"></div>
        </div>
    </header>
    <main class="admin-main">
