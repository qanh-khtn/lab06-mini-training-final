<?php
/** @var int|null $leadCount */
/** @var int|null $paymentCount */
/** @var bool $dbOk */
?>
<div class="<?= !is_logged_in() ? 'public-home-container' : '' ?>">
    <div class="bento-grid">
        <!-- Hero Banner -->
        <div class="dashboard-hero">
            <h1>Chào mừng đến với CRM</h1>
            <p>Quản lý tư vấn học viên và thanh toán học phí trong một hệ thống thống nhất, dễ sử dụng.</p>
            
            <div class="mt-lg">
                <?php if (!$dbOk): ?>
                    <span class="badge badge-cancelled">
                        <span class="material-symbols-outlined icon-sm">error</span>
                        Chưa kết nối được cơ sở dữ liệu — vui lòng kiểm tra cấu hình.
                    </span>
                <?php else: ?>
                    <span class="badge badge-enrolled">
                        <span class="material-symbols-outlined icon-sm">check_circle</span>
                        Hệ thống hoạt động ổn định &amp; Kết nối Database thành công
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Feature Cards -->
        <a class="feature-card" href="/leads">
            <div class="feature-icon">
                <span class="material-symbols-outlined">group</span>
            </div>
            <h3>Quản lý Lead tư vấn</h3>
            <p>Quản lý danh sách học viên tiềm năng, theo dõi quá trình tư vấn và trạng thái chăm sóc từng khách hàng.</p>
            <span class="stat"><?= $leadCount === null ? '—' : number_format($leadCount) ?> leads</span>
        </a>

        <a class="feature-card" href="/payments">
            <div class="feature-icon">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <h3>Thanh toán học phí</h3>
            <p>Quản lý phiếu thu học phí theo từng khóa học, theo dõi trạng thái thanh toán và lịch sử giao dịch.</p>
            <span class="stat"><?= $paymentCount === null ? '—' : number_format($paymentCount) ?> phiếu thu</span>
        </a>

        <a class="feature-card" href="/public-leads/create">
            <div class="feature-icon">
                <span class="material-symbols-outlined">campaign</span>
            </div>
            <h3>Form đăng ký tư vấn</h3>
            <p>Khách hàng tiềm năng có thể điền thông tin để nhận tư vấn trực tiếp từ đội ngũ chuyên viên của chúng tôi.</p>
            <span class="stat stat-cta">Bắt đầu đăng ký →</span>
        </a>
    </div>
</div>
