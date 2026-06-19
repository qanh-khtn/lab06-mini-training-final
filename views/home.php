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
        <div class="feature-icon">A</div>
        <h2>Lead tư vấn</h2>
        <p class="muted">Danh sách + tìm kiếm + phân trang + sort. Email không trùng (UNIQUE), có trạng thái chăm sóc.</p>
        <p class="stat"><?= $leadCount === null ? '—' : h($leadCount) ?> lead</p>
    </a>
    <a class="card feature-card" href="/payments">
        <div class="feature-icon">B</div>
        <h2>Thanh toán học phí</h2>
        <p class="muted">CRUD phiếu thanh toán học phí. Mã thanh toán (payment_code) không trùng (UNIQUE).</p>
        <p class="stat"><?= $paymentCount === null ? '—' : h($paymentCount) ?> phiếu</p>
    </a>
</div>

<div class="dev-note">
    <strong>Luồng kiến trúc:</strong>
    Browser → public/index.php → Router → Controller → Repository → PDO → MySQL → View/Redirect → Browser.
</div>
