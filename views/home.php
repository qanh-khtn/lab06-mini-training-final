<?php
/** @var int|null $leadCount */
/** @var int|null $paymentCount */
/** @var bool $dbOk */
?>

<div class="home-hero">
    <h1>Chào mừng đến với CRM</h1>
    <p>Quản lý tư vấn học viên và thanh toán học phí trong một hệ thống thống nhất, dễ sử dụng.</p>

    <?php if (!$dbOk): ?>
        <div class="db-error">⚠ Chưa kết nối được cơ sở dữ liệu — vui lòng kiểm tra cấu hình.</div>
    <?php else: ?>
    <div class="home-stats-row">
        <div class="home-stat">
            <span class="stat-num"><?= $leadCount === null ? '—' : number_format($leadCount) ?></span>
            <span class="stat-lbl">Khách hàng tư vấn</span>
        </div>
        <div class="home-stat">
            <span class="stat-num"><?= $paymentCount === null ? '—' : number_format($paymentCount) ?></span>
            <span class="stat-lbl">Phiếu thanh toán</span>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="grid-2">
    <a class="card feature-card" href="/leads">
        <div class="feature-icon" style="background: linear-gradient(135deg,#2563eb,#1d4ed8);">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <h3>Khách hàng tư vấn</h3>
        <p>Quản lý danh sách học viên tiềm năng, theo dõi quá trình tư vấn và trạng thái chăm sóc từng khách hàng.</p>
        <span class="stat"><?= $leadCount === null ? '—' : number_format($leadCount) ?> khách hàng</span>
    </a>

    <a class="card feature-card" href="/payments">
        <div class="feature-icon" style="background: linear-gradient(135deg,#7c3aed,#6d28d9);">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <h3>Thanh toán học phí</h3>
        <p>Quản lý phiếu thu học phí theo từng khóa học, theo dõi trạng thái thanh toán và lịch sử giao dịch.</p>
        <span class="stat"><?= $paymentCount === null ? '—' : number_format($paymentCount) ?> phiếu</span>
    </a>
</div>
