<?php
/** @var array  $entries
 *  @var int    $totalLines
 *  @var string $q
 *  @var string $level
 */
?>

<section class="page-head">
    <div>
        <h1>Nhật ký hệ thống</h1>
        <p class="muted">Đọc từ <code>storage/logs/app.log</code> &mdash; <?= $totalLines ?> dòng tổng cộng, hiển thị mới nhất trước</p>
    </div>
    <a class="btn btn-secondary" href="/">&#8592; Trang chủ</a>
</section>

<div class="log-toolbar card" style="margin-bottom:16px;">
    <form method="get" action="/admin/logs" class="log-filter-form">
        <input
            class="input"
            type="search"
            name="q"
            value="<?= h($q) ?>"
            placeholder="Tìm trong log..."
            style="flex:1;min-width:200px;"
        >
        <select class="input" name="level" style="width:160px;">
            <option value="" <?= $level === '' ? 'selected' : '' ?>>Tất cả mức độ</option>
            <option value="error"   <?= $level === 'error'   ? 'selected' : '' ?>>Lỗi</option>
            <option value="warning" <?= $level === 'warning' ? 'selected' : '' ?>>Cảnh báo</option>
            <option value="info"    <?= $level === 'info'    ? 'selected' : '' ?>>Thông tin</option>
        </select>
        <button class="btn btn-primary" type="submit">Lọc</button>
        <?php if ($q !== '' || $level !== ''): ?>
            <a class="btn btn-secondary" href="/admin/logs">Xóa bộ lọc</a>
        <?php endif; ?>
    </form>
    <p class="muted" style="margin-top:8px;font-size:13px;">
        Hiển thị <strong><?= count($entries) ?></strong> dòng<?= ($q !== '' || $level !== '') ? ' (đang lọc)' : '' ?>
    </p>
</div>

<?php if ($entries === []): ?>
    <div class="card" style="text-align:center;padding:48px 24px;">
        <p style="color:var(--text-2);font-size:15px;">
            <?= $q !== '' || $level !== '' ? 'Không tìm thấy dòng log nào khớp với bộ lọc.' : 'File log trống hoặc chưa có lỗi nào được ghi.' ?>
        </p>
    </div>
<?php else: ?>
<div class="log-list">
    <?php foreach ($entries as $i => $entry): ?>
        <?php
            $lvlClass = match($entry['level']) {
                'error'   => 'log-error',
                'warning' => 'log-warning',
                default   => 'log-info',
            };
            $lvlLabel = match($entry['level']) {
                'error'   => 'LỖI',
                'warning' => 'CẢNH BÁO',
                default   => 'INFO',
            };
        ?>
        <div class="log-entry <?= $lvlClass ?>">
            <div class="log-header">
                <span class="log-badge log-badge-<?= $entry['level'] ?>"><?= $lvlLabel ?></span>
                <?php if ($entry['ts']): ?>
                    <span class="log-ts"><?= h($entry['ts']) ?></span>
                <?php endif; ?>
            </div>
            <pre class="log-msg" id="log-<?= $i ?>"><?= h($entry['msg']) ?></pre>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
