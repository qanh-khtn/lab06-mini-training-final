<?php
/** @var int|null $leadCount */
/** @var int|null $paymentCount */
/** @var bool $dbOk */
?>
<section class="page-head">
    <div>
        <h1>Mini Training Center CRM</h1>
        <p class="muted">Quản lý lead tư vấn &amp; thanh toán học phí — PDO · Repository · CRUD · Search/Pagination · Unique · Index</p>
    </div>
    <a class="btn btn-secondary" href="/health">Kiểm tra /health</a>
</section>

<?php if (!$dbOk): ?>
    <div class="alert alert-warning">
        ⚠ Chưa kết nối được database. Hãy tạo DB bằng <code>database/schema.sql</code>, nạp <code>database/seed.sql</code>
        và chỉnh <code>config/database.php</code> cho khớp máy bạn.
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
        <h3>Lead tư vấn</h3>
        <p>Danh sách + tìm kiếm + phân trang + sort theo cột. Email không trùng (UNIQUE), theo dõi trạng thái chăm sóc.</p>
        <p class="stat"><?= $leadCount === null ? '—' : h($leadCount) ?> lead</p>
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
        <p>CRUD phiếu thanh toán học phí. Mã thanh toán (payment_code) không trùng (UNIQUE), theo dõi trạng thái thanh toán.</p>
        <p class="stat"><?= $paymentCount === null ? '—' : h($paymentCount) ?> phiếu</p>
    </a>
</div>

<div class="dev-note">
    <strong>Luồng kiến trúc:</strong>
    Browser → public/index.php → Router → Controller → Repository → PDO → MySQL → View/Redirect → Browser.
</div>
