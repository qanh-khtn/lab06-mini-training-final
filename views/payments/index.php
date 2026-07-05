<?php
/** @var array $payments */
/** @var string $q */
/** @var string $statusFilter */
/** @var string $sort */
/** @var string $dir */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var int $page */
/** @var int $lastPage */
/** @var int $total */
/** @var array $statusLabels */
$isFiltered = $q !== '' || $statusFilter !== '' || $dateFrom !== '' || $dateTo !== '';
?>
<section class="page-head">
    <div>
        <h1>Quản lý Thanh toán học phí</h1>
        <p class="muted">Tổng <?= h($total) ?> phiếu thanh toán<?= $isFiltered ? ' (đang lọc)' : '' ?></p>
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
    <label class="text-muted text-xs" for="date_from">Từ ngày</label>
    <input type="date" id="date_from" name="date_from" value="<?= h($dateFrom) ?>">
    <label class="text-muted text-xs" for="date_to">Đến ngày</label>
    <input type="date" id="date_to" name="date_to" value="<?= h($dateTo) ?>">
    <input type="hidden" name="sort" value="<?= h($sort) ?>">
    <input type="hidden" name="dir" value="<?= h($dir) ?>">
    <button class="btn btn-primary" type="submit">Lọc</button>
    <?php if ($isFiltered): ?><a class="btn btn-secondary" href="/payments">Xóa lọc</a><?php endif; ?>
    <a class="btn btn-secondary" href="/payments/export?<?= h(query_string()) ?>">
        <span class="material-symbols-outlined icon-sm">download</span> Xuất CSV
    </a>
</form>

<?php if (is_admin()): ?>
<form id="bulk-form" method="post" action="/payments/bulk-delete" onsubmit="return confirm('Xóa các phiếu thanh toán đã chọn?')">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
</form>
<div class="bulk-actions" id="bulk-actions" style="display:none;">
    <span id="bulk-count" class="text-muted text-sm">Đã chọn 0 dòng</span>
    <button class="btn btn-sm btn-danger" type="submit" form="bulk-form">
        <span class="material-symbols-outlined icon-sm">delete</span> Xóa đã chọn
    </button>
</div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <?php if (is_admin()): ?>
                <th><input type="checkbox" id="check-all"></th>
                <?php endif; ?>
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
            <tr><td colspan="<?= is_admin() ? 10 : 9 ?>" class="muted" style="text-align:center;padding:24px">Không có dữ liệu phù hợp.</td></tr>
        <?php else: foreach ($payments as $p): ?>
            <tr>
                <?php if (is_admin()): ?>
                <td><input type="checkbox" class="row-check" name="ids[]" value="<?= (int) $p['id'] ?>" form="bulk-form"></td>
                <?php endif; ?>
                <td><?= h($p['id']) ?></td>
                <td><?= h($p['payment_code']) ?></td>
                <td><?= h($p['student_name']) ?></td>
                <td><?= h($p['student_email']) ?></td>
                <td><?= h($p['course_name']) ?></td>
                <td class="text-right amount"><?= h(number_format((float) $p['amount'], 0, ',', '.')) ?> đ</td>
                <td><span class="badge badge-<?= h($p['status']) ?>"><?= h($statusLabels[$p['status']] ?? $p['status']) ?></span></td>
                <td><?= h($p['created_at']) ?></td>
                <td class="actions-cell">
                    <a class="btn btn-sm btn-secondary" href="/payments/edit?id=<?= h($p['id']) ?>">
                        <span class="material-symbols-outlined icon-sm">edit</span> Sửa
                    </a>
                    <?php if (is_admin()): ?>
                    <form class="inline-form" method="post" action="/payments/delete" onsubmit="return confirm('Xóa phiếu thanh toán này?')">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= h($p['id']) ?>">
                        <button class="btn btn-sm btn-danger" type="submit">
                            <span class="material-symbols-outlined icon-sm">delete</span> Xóa
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="/payments?<?= h(query_string(['page' => $page - 1])) ?>">
            <span class="material-symbols-outlined icon-md" style="margin-right:4px;">arrow_back</span> Trước
        </a>
    <?php else: ?>
        <span class="disabled">
            <span class="material-symbols-outlined icon-md" style="margin-right:4px;">arrow_back</span> Trước
        </span>
    <?php endif; ?>
    <span class="current">Trang <?= h($page) ?> / <?= h($lastPage) ?></span>
    <?php if ($page < $lastPage): ?>
        <a href="/payments?<?= h(query_string(['page' => $page + 1])) ?>">
            Sau <span class="material-symbols-outlined icon-md" style="margin-left:4px;">arrow_forward</span>
        </a>
    <?php else: ?>
        <span class="disabled">
            Sau <span class="material-symbols-outlined icon-md" style="margin-left:4px;">arrow_forward</span>
        </span>
    <?php endif; ?>
</div>
