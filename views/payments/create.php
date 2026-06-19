<?php
/** @var array $errors @var array $old @var array $statusLabels */
?>
<section class="page-head">
    <div>
        <h1>Thêm Thanh toán học phí</h1>
        <p class="muted">Mã thanh toán phải là duy nhất.</p>
    </div>
    <a class="btn btn-secondary" href="/payments">← Danh sách</a>
</section>

<?php
$action = '/payments/store';
$submitLabel = 'Lưu phiếu';
require view_path('payments/_form');
?>
