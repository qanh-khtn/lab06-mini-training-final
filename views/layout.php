<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$flashTypeMap = [
    'success' => 'success',
    'danger'  => 'danger',
    'error'   => 'danger',
    'warning' => 'warning',
    'info'    => 'info',
];
?>
<!doctype html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title ?? 'Mini Training Center CRM') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
    <script src="/assets/app.js"></script>
</head>
<?php
$showSidebar = is_logged_in() && !in_array($currentPath, ['/login', '/register', '/public-leads/create']);
?>
<body class="<?= $showSidebar ? 'flex-layout' : 'no-sidebar' ?>">
    <?php if ($showSidebar): ?>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="brand-logo">
                <span class="material-symbols-outlined" style="color:#fff; font-size: 20px;">school</span>
            </span>
            <div>
                <h2>MTC Admin</h2>
                <p>Operational Suite</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a class="<?= $currentPath === '/' ? 'active' : '' ?>" href="/">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class="<?= str_starts_with($currentPath, '/leads') ? 'active' : '' ?>" href="/leads">
                        <span class="material-symbols-outlined">group</span>
                        <span>Leads</span>
                    </a>
                </li>
                <li>
                    <a class="<?= str_starts_with($currentPath, '/payments') ? 'active' : '' ?>" href="/payments">
                        <span class="material-symbols-outlined">payments</span>
                        <span>Payments</span>
                    </a>
                </li>
                <li>
                    <a class="<?= $currentPath === '/stats' ? 'active' : '' ?>" href="/stats">
                        <span class="material-symbols-outlined">analytics</span>
                        <span>Stats</span>
                    </a>
                </li>
                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <li>
                    <a class="<?= $currentPath === '/admin/users/pending' ? 'active' : '' ?>" href="/admin/users/pending">
                        <span class="material-symbols-outlined">admin_panel_settings</span>
                        <span>Admin</span>
                    </a>
                </li>
                <li>
                    <a class="<?= $currentPath === '/admin/logs' ? 'active' : '' ?>" href="/admin/logs">
                        <span class="material-symbols-outlined">history</span>
                        <span>Logs</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <span class="username"><?= h($_SESSION['user_name'] ?? '') ?></span>
                <span class="role"><?= h($_SESSION['user_role'] ?? '') ?></span>
            </div>
            <form method="POST" action="/logout" id="logout-form">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <button type="submit" class="logout-link">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Sidebar Backdrop for Mobile -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <?php endif; ?>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <?php if ($showSidebar): ?>
        <!-- TopNavBar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menu-toggle" aria-label="Toggle Menu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="topbar-search">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" placeholder="Tìm kiếm nhanh..." disabled>
                </div>
            </div>
            <div class="topbar-right">
                <button class="topbar-btn" id="theme-toggle" type="button">
                    <span class="material-symbols-outlined sun-icon">light_mode</span>
                    <span class="material-symbols-outlined moon-icon">dark_mode</span>
                </button>
                <button class="topbar-btn" type="button" title="Thông báo">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <button class="topbar-btn" type="button" title="Hỗ trợ">
                    <span class="material-symbols-outlined">help</span>
                </button>
                <div class="user-avatar" title="<?= h($_SESSION['user_name'] ?? '') ?>">
                    <?= h(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2))) ?>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <!-- Canvas -->
        <main class="canvas">
            <?php require view_path($view); ?>
        </main>
    </div>

    <!-- Toast container -->
    <?php $flashes = flash_get(); ?>
    <?php if ($flashes): ?>
    <div class="toast-container" id="toast-container">
        <?php foreach ($flashes as $type => $messages): ?>
            <?php $tone = $flashTypeMap[$type] ?? 'info'; ?>
            <?php foreach ($messages as $message): ?>
                <div class="toast toast-<?= h($tone) ?>" role="alert">
                    <span class="toast-icon">
                        <?php if ($tone === 'success'): ?>
                            <span class="material-symbols-outlined">check_circle</span>
                        <?php elseif ($tone === 'danger'): ?>
                            <span class="material-symbols-outlined">error</span>
                        <?php elseif ($tone === 'warning'): ?>
                            <span class="material-symbols-outlined">warning</span>
                        <?php else: ?>
                            <span class="material-symbols-outlined">info</span>
                        <?php endif; ?>
                    </span>
                    <span class="toast-msg"><?= h($message) ?></span>
                    <button class="toast-close" aria-label="Đóng">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                    <div class="toast-progress"></div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!$showSidebar): ?>
    <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Chuyển giao diện sáng/tối">
        <span class="theme-toggle-inner">
            <span class="material-symbols-outlined sun-icon">light_mode</span>
            <span class="material-symbols-outlined moon-icon">dark_mode</span>
        </span>
    </button>
    <?php endif; ?>
</body>
</html>
