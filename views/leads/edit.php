<?php
/** @var array $errors @var array $old @var int $id @var array $courseLabels @var array $careLabels */
?>
<section class="page-head">
    <div>
        <h1>Sửa Lead tư vấn</h1>
        <p class="muted">ID #<?= h($id) ?> • Email phải là duy nhất.</p>
    </div>
    <a class="btn btn-secondary" href="/leads">← Danh sách</a>
</section>

<?php
$action = '/leads/update';
$submitLabel = 'Cập nhật lead';
require view_path('leads/_form');
?>
