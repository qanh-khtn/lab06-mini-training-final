<?php
/** @var array $payments */
/** @var string $q */
/** @var string $statusFilter */
/** @var string $sort */
/** @var string $dir */
/** @var int $page */
/** @var int $lastPage */
/** @var int $total */
/** @var array $statusLabels */
?>
<section class="page-head">
    <div>
        <h1>Quản lý Thanh toán học phí</h1>
        <p class="muted">Tổng <?= h($total) ?> phiếu • Trang <?= h($page) ?>/<?= h($lastPage) ?> • Sort: <?= h($sort) ?> <?= h(strtoupper($dir)) ?></p>
    </div>
    <a class="btn btn-primary" href="/payments/create">+ Thêm thanh toán</a>
</section>

<form class="toolbar" method="get" action="/payments">
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Tìm theo mã / tên học viên / email">
    <select name="status">
        <option value="">-- Tất cả trạng thái --</option>
        <?php foreach ($statusLabels as $key => $label): ?>
            <option value="<?= h($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="sort" value="<?= h($sort) ?>">
    <input type="hidden" name="dir" value="<?= h($dir) ?>">
    <button class="btn btn-primary" type="submit">Lọc</button>
    <?php if ($q !== '' || $statusFilter !== ''): ?><a class="btn btn-secondary" href="/payments">Xóa lọc</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th><a href="/payments<?= h(sort_url('id', $sort, $dir)) ?>">ID<?= h(sort_caret('id', $sort, $dir)) ?></a></th>
                <th><a href="/payments<?= h(sort_url('payment_code', $sort, $dir)) ?>">Mã TT<?= h(sort_caret('payment_code', $sort, $dir)) ?></a></th>
                <th><a href="/payments<?= h(sort_url('student_name', $sort, $dir)) ?>">Học viên<?= h(sort_caret('student_name', $sort, $dir)) ?></a></th>
                <th>Email</th>
                <th>Khóa học</th>
                <th class="text-right"><a href="/payments<?= h(sort_url('amount', $sort, $dir)) ?>">Số tiền<?= h(sort_caret('amount', $sort, $dir)) ?></a></th>
                <th><a href="/payments<?= h(sort_url('status', $sort, $dir)) ?>">Trạng thái<?= h(sort_caret('status', $sort, $dir)) ?></a></th>
                <th><a href="/payments<?= h(sort_url('created_at', $sort, $dir)) ?>">Ngày tạo<?= h(sort_caret('created_at', $sort, $dir)) ?></a></th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($payments === []): ?>
            <tr><td colspan="9" class="muted" style="text-align:center;padding:24px">Không có dữ liệu phù hợp.</td></tr>
        <?php else: foreach ($payments as $p): ?>
            <tr>
                <td><?= h($p['id']) ?></td>
                <td><?= h($p['payment_code']) ?></td>
                <td><?= h($p['student_name']) ?></td>
                <td><?= h($p['student_email']) ?></td>
                <td><?= h($p['course_name']) ?></td>
                <td class="text-right amount"><?= h(number_format((float) $p['amount'], 0, ',', '.')) ?> đ</td>
                <td><span class="badge badge-<?= h($p['status']) ?>"><?= h($statusLabels[$p['status']] ?? $p['status']) ?></span></td>
                <td><?= h($p['created_at']) ?></td>
                <td class="actions-cell">
                    <a class="btn btn-sm btn-secondary" href="/payments/edit?id=<?= h($p['id']) ?>">Sửa</a>
                    <form class="inline-form" method="post" action="/payments/delete" onsubmit="return confirm('Xóa phiếu thanh toán này?')">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= h($p['id']) ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="/payments?<?= h(query_string(['page' => $page - 1])) ?>">← Trước</a>
    <?php else: ?>
        <span class="disabled">← Trước</span>
    <?php endif; ?>
    <span class="current">Trang <?= h($page) ?> / <?= h($lastPage) ?></span>
    <?php if ($page < $lastPage): ?>
        <a href="/payments?<?= h(query_string(['page' => $page + 1])) ?>">Sau →</a>
    <?php else: ?>
        <span class="disabled">Sau →</span>
    <?php endif; ?>
</div>
