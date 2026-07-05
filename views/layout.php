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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700;9..144,900&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
    <script src="/assets/app.js"></script>
</head>
<?php
// /public-leads/create không loại trừ ở đây: nhân viên đã đăng nhập bấm vào
// link "Form công khai" trong sidebar vẫn cần thấy sidebar như mọi trang khác.
// Trang này chỉ hiện topbar công khai khi KHÔNG đăng nhập (khách vãng lai).
$showSidebar = is_logged_in() && !in_array($currentPath, ['/login', '/register']);
?>
<body class="<?= $showSidebar ? 'flex-layout' : 'no-sidebar' ?>">
    <?php if ($showSidebar): ?>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div>
                <h2>CRM Đào Tạo</h2>
                <p>Hệ Thống Quản Lý</p>
            </div>
            <button class="sidebar-toggle" id="sidebar-toggle" title="Ẩn/hiện sidebar">
                ◀
            </button>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a class="<?= $currentPath === '/' ? 'active' : '' ?>" href="/">
                        <span>Trang chủ</span>
                    </a>
                </li>
                <li>
                    <a class="<?= str_starts_with($currentPath, '/leads') ? 'active' : '' ?>" href="/leads">
                        <span>Lead tư vấn</span>
                    </a>
                </li>
                <li>
                    <a class="<?= str_starts_with($currentPath, '/payments') ? 'active' : '' ?>" href="/payments">
                        <span>Thanh toán</span>
                    </a>
                </li>
                <li>
                    <a class="<?= $currentPath === '/stats' ? 'active' : '' ?>" href="/stats">
                        <span>Thống kê</span>
                    </a>
                </li>
                <li>
                    <a class="<?= str_starts_with($currentPath, '/public-leads') ? 'active' : '' ?>" href="/public-leads/create">
                        <span>Form công khai</span>
                    </a>
                </li>
                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <li style="border-top: 1px solid var(--border); margin-top: 8px; padding-top: 8px;">
                    <a class="<?= $currentPath === '/admin/users/pending' ? 'active' : '' ?>" href="/admin/users/pending">
                        <span>Phê duyệt NV</span>
                    </a>
                </li>
                <li>
                    <a class="<?= $currentPath === '/admin/logs' ? 'active' : '' ?>" href="/admin/logs">
                        <span>Nhật ký</span>
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
                <div class="topbar-search" id="quick-search-form">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="quick-search-input" placeholder="Tìm kiếm lead, email, SDT..." autocomplete="off">
                    <div class="search-results" id="search-results"></div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="topbar-btn" id="theme-toggle" type="button" title="Chế độ sáng/tối">
                    <span class="material-symbols-outlined sun-icon">light_mode</span>
                    <span class="material-symbols-outlined moon-icon">dark_mode</span>
                </button>
                <button class="topbar-btn" id="notif-btn" type="button" title="Thông báo" aria-label="Notifications" style="position:relative;">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notif-badge">3</span>
                </button>
                <button class="topbar-btn" id="help-btn" type="button" title="Trợ giúp" aria-label="Help">
                    <span class="material-symbols-outlined">help</span>
                </button>
                <div class="user-avatar" title="<?= h($_SESSION['user_name'] ?? '') ?>">
                    <?= h(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2))) ?>
                </div>
            </div>
        </header>

        <!-- Notification Dropdown -->
        <div class="notif-dropdown" id="notif-menu">
            <div class="notif-list">
                <a href="/leads" class="notif-item unread">
                    <strong>Lead mới: Nguyễn Văn A</strong>
                    <p>Vừa đăng ký tư vấn khóa Web</p>
                    <span class="notif-time">5 phút trước</span>
                </a>
                <a href="/payments" class="notif-item">
                    <strong>Thanh toán mới</strong>
                    <p>Lê Hoàng Cường thanh toán Khóa Mobile</p>
                    <span class="notif-time">20 phút trước</span>
                </a>
                <a href="/leads" class="notif-item">
                    <strong>Cần chăm sóc</strong>
                    <p>Trần Thị Bình chưa hoàn tất ghi danh</p>
                    <span class="notif-time">1 giờ trước</span>
                </a>
            </div>
        </div>

        <!-- Help Modal -->
        <dialog id="help-modal" class="help-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Trợ giúp & Hướng dẫn</h2>
                    <button class="btn-close" type="button">&times;</button>
                </div>
                <div class="modal-body">
                    <h3>Quản lý Lead</h3>
                    <p>Sử dụng mục "Lead tư vấn" để xem danh sách khách hàng tiềm năng, cập nhật trạng thái chăm sóc và quản lý thông tin liên hệ.</p>

                    <h3>Thanh toán học phí</h3>
                    <p>Mục "Thanh toán" cho phép bạn ghi lại phiếu thu, theo dõi tình trạng thanh toán và lịch sử giao dịch của các khóa học.</p>

                    <h3>Thống kê & báo cáo</h3>
                    <p>Xem các KPI chính, biểu đồ doanh thu, phân bổ lead theo trạng thái và top khóa học được đăng ký nhất trong mục "Thống kê".</p>

                    <h3>Tìm kiếm nhanh</h3>
                    <p>Sử dụng thanh tìm kiếm ở đầu trang để tìm kiếm lead hoặc thanh toán theo tên, email hoặc số điện thoại.</p>

                    <h3>Chế độ sáng/tối</h3>
                    <p>Nhấp vào nút mặt trăng/mặt trời ở góc phải để chuyển đổi giữa chế độ sáng và tối theo sở thích của bạn.</p>

                    <h3>Phím tắt</h3>
                    <p>
                        <kbd>Ctrl</kbd> + <kbd>K</kbd> — focus vào ô tìm kiếm nhanh<br>
                        <kbd>Esc</kbd> — đóng kết quả tìm kiếm, thông báo hoặc hộp thoại đang mở
                    </p>
                </div>
            </div>
        </dialog>
        <?php else: ?>
        <!-- Public Navigation Bar -->
        <header class="public-header">
            <div class="public-header-container">
                <a href="/" class="public-brand">
                    <div class="brand-logo">
                        <span class="material-symbols-outlined" style="color:white; font-size:20px;">school</span>
                    </div>
                    <span class="brand-name">CRM Đào Tạo</span>
                </a>
                
                <nav class="public-nav">
                    <a href="/" class="<?= $currentPath === '/' ? 'active' : '' ?>">Trang chủ</a>
                    <a href="/public-leads/create" class="<?= $currentPath === '/public-leads/create' ? 'active' : '' ?>">Đăng ký tư vấn</a>
                    <a href="/register" class="<?= $currentPath === '/register' ? 'active' : '' ?>">Đăng ký nhân viên</a>
                </nav>
                
                <div class="public-actions">
                    <button class="topbar-btn" id="theme-toggle" type="button" title="Chế độ sáng/tối">
                        <span class="material-symbols-outlined sun-icon">light_mode</span>
                        <span class="material-symbols-outlined moon-icon">dark_mode</span>
                    </button>
                    <?php if (is_logged_in()): ?>
                        <a href="/leads" class="btn btn-primary btn-sm">Vào Dashboard</a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary btn-sm">Đăng nhập</a>
                    <?php endif; ?>
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


</body>
</html>
