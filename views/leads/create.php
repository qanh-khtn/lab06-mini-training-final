<?php
/** @var array $errors @var array $old @var array $courseLabels @var array $careLabels */
?>
<section class="page-head">
    <div>
        <h1>Thêm Lead tư vấn</h1>
        <p class="muted">Email phải là duy nhất trong hệ thống.</p>
    </div>
    <a class="btn btn-secondary" href="/leads">← Danh sách</a>
</section>

<?php
$action = '/leads/store';
$submitLabel = 'Lưu lead';
require view_path('leads/_form');
?>
