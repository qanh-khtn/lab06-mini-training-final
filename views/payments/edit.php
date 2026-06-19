<?php
/** @var array $errors @var array $old @var int $id @var array $statusLabels */
?>
<section class="page-head">
    <div>
        <h1>Sửa Thanh toán học phí</h1>
        <p class="muted">ID #<?= h($id) ?> • Mã thanh toán phải là duy nhất.</p>
    </div>
    <a class="btn btn-secondary" href="/payments">← Danh sách</a>
</section>

<?php
$action = '/payments/update';
$submitLabel = 'Cập nhật phiếu';
require view_path('payments/_form');
?>
