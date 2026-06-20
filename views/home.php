<?php
/** @var int|null $leadCount */
/** @var int|null $paymentCount */
/** @var bool $dbOk */
?>
<section class="page-head">
    <div>
        <h1>Mini Training Center CRM</h1>
        <p class="muted">Hệ thống quản lý tư vấn học viên và thanh toán học phí</p>
    </div>
</section>

<?php if (!$dbOk): ?>
    <div class="alert alert-warning">
        ⚠ Chưa kết nối được database. Vui lòng kiểm tra lại cấu hình hệ thống.
    </div>
<?php endif; ?>

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
        <p>Quản lý danh sách học viên tiềm năng, theo dõi quá trình tư vấn và trạng thái chăm sóc.</p>
        <p class="stat"><?= $leadCount === null ? '—' : h($leadCount) ?> khách hàng</p>
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
        <p>Quản lý phiếu thu học phí theo từng khóa học, theo dõi trạng thái thanh toán của học viên.</p>
        <p class="stat"><?= $paymentCount === null ? '—' : h($paymentCount) ?> phiếu</p>
    </a>
</div>
