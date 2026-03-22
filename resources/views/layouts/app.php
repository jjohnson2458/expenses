<?php
/**
 * MyExpenses - Main Application Layout
 *
 * Views use output buffering:
 *   ob_start();
 *   // ... view markup ...
 *   $content = ob_get_clean();
 *   $title = 'Page Name';
 *   require __DIR__ . '/../layouts/app.php';
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

$title       = $title ?? 'Dashboard';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$userName    = htmlspecialchars($_SESSION['user_name'] ?? 'Guest', ENT_QUOTES, 'UTF-8');
$userEmail   = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$role        = $_SESSION['user_role'] ?? 'user';
$lang        = $_SESSION['lang'] ?? 'en';
$csrfToken   = $_SESSION['csrf_token'] ?? '';
$flash       = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function navActive(string $href, string $current): string {
    if ($href === '/dashboard') {
        return ($current === '/' || $current === '/dashboard') ? 'active' : '';
    }
    return str_starts_with($current, $href) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="MyExpenses — Personal and small-business expense tracking, reporting, and budgeting.">
    <meta name="author" content="J.J. Johnson">
    <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> - MyExpenses">
    <meta property="og:description" content="Track expenses, generate reports, and stay on budget with MyExpenses.">
    <meta property="og:type" content="website">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> - MyExpenses</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- App CSS -->
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar" role="navigation" aria-label="Main navigation">
    <div class="sidebar-brand">
        <a href="/dashboard" class="sidebar-brand-link">
            <i class="bi bi-wallet2"></i>
            <span>MyExpenses</span>
        </a>
    </div>

    <ul class="sidebar-nav list-unstyled">
        <li>
            <a href="/dashboard" class="sidebar-nav-link <?= navActive('/dashboard', $currentPath) ?>">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/expenses" class="sidebar-nav-link <?= navActive('/expenses', $currentPath) ?>">
                <i class="bi bi-receipt"></i> <span>Expenses</span>
            </a>
        </li>
        <li>
            <a href="/categories" class="sidebar-nav-link <?= navActive('/categories', $currentPath) ?>">
                <i class="bi bi-tags"></i> <span>Categories</span>
            </a>
        </li>
        <li>
            <a href="/reports" class="sidebar-nav-link <?= navActive('/reports', $currentPath) ?>">
                <i class="bi bi-file-earmark-text"></i> <span>Reports</span>
            </a>
        </li>
        <li>
            <a href="/recurring" class="sidebar-nav-link <?= navActive('/recurring', $currentPath) ?>">
                <i class="bi bi-arrow-repeat"></i> <span>Recurring</span>
            </a>
        </li>

        <li class="sidebar-separator"></li>

        <li>
            <a href="/import" class="sidebar-nav-link <?= navActive('/import', $currentPath) ?>">
                <i class="bi bi-upload"></i> <span>Import</span>
            </a>
        </li>
        <li>
            <a href="#exportSubmenu" class="sidebar-nav-link" data-bs-toggle="collapse" aria-expanded="false">
                <i class="bi bi-download"></i>
                <span>Export</span>
                <i class="bi bi-chevron-down ms-auto small"></i>
            </a>
            <ul id="exportSubmenu" class="sidebar-submenu collapse list-unstyled">
                <li><a href="/export/csv" class="sidebar-nav-link sub-link">CSV</a></li>
                <li><a href="/export/quickbooks" class="sidebar-nav-link sub-link">QuickBooks</a></li>
                <li><a href="/export/calendar" class="sidebar-nav-link sub-link">Calendar</a></li>
            </ul>
        </li>

        <li class="sidebar-separator"></li>

        <li>
            <a href="/settings" class="sidebar-nav-link <?= navActive('/settings', $currentPath) ?>">
                <i class="bi bi-gear"></i> <span>Settings</span>
            </a>
        </li>
        <?php if ($role === 'admin'): ?>
        <li>
            <a href="/admin/errors" class="sidebar-nav-link <?= navActive('/admin/errors', $currentPath) ?>">
                <i class="bi bi-exclamation-triangle"></i> <span>Error Log</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= $userName ?></div>
            <div class="sidebar-user-email"><?= $userEmail ?></div>
        </div>
        <div class="sidebar-footer-actions">
            <div class="sidebar-lang-switcher">
                <a href="/lang/en" class="lang-btn <?= $lang === 'en' ? 'lang-active' : '' ?>">EN</a>
                <span class="lang-divider">|</span>
                <a href="/lang/es" class="lang-btn <?= $lang === 'es' ? 'lang-active' : '' ?>">ES</a>
            </div>
            <a href="/logout" class="sidebar-logout" title="Log out">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div id="main-content" class="main-content">

    <!-- Mobile sidebar toggle -->
    <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-md-none sidebar-toggle" type="button" aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
    </button>

    <!-- Top bar -->
    <header class="top-bar">
        <div class="top-bar-left">
            <h1 class="top-bar-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <div class="top-bar-right">
            <form class="top-bar-search" action="/expenses" method="GET">
                <div class="input-group input-group-sm">
                    <input type="search" name="q" class="form-control" placeholder="Search expenses..."
                           value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <div class="dropdown ms-3">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-lg-inline"><?= $userName ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/settings"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Log out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Flash messages -->
    <?php if ($flash): ?>
    <div class="container-fluid px-4 mt-3">
        <?php
        $flashType = $flash['type'] ?? 'info';
        $alertClass = match ($flashType) {
            'success' => 'alert-success',
            'error', 'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info',
        };
        ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show flash-message" role="alert">
            <?= htmlspecialchars($flash['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page content -->
    <div class="content-wrapper container-fluid px-4 py-4">
        <?= $content ?? '' ?>
    </div>

    <!-- Footer -->
    <footer class="app-footer">
        <span>&copy; 2026 VisionQuest Services LLC</span>
    </footer>
</div>

<!-- jQuery 3.7 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- App JS -->
<script src="/js/app.js"></script>

</body>
</html>
