<?php
/** @var array $leads */
/** @var string $q */
/** @var string $statusFilter */
/** @var string $sort */
/** @var string $dir */
/** @var int $page */
/** @var int $lastPage */
/** @var int $total */
/** @var array $courseLabels */
/** @var array $careLabels */
?>
<section class="page-head">
    <div>
        <h1>Quản lý Lead tư vấn</h1>
        <p class="muted">Tổng <?= h($total) ?> khách hàng tư vấn<?= $q !== '' || $statusFilter !== '' ? ' (đang lọc)' : '' ?></p>
    </div>
    <a class="btn btn-primary" href="/leads/create">+ Thêm lead</a>
</section>

<form class="toolbar" method="get" action="/leads">
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Tìm theo tên / email / SĐT">
    <select name="status">
        <option value="">-- Tất cả trạng thái --</option>
        <?php foreach ($careLabels as $key => $label): ?>
            <option value="<?= h($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="sort" value="<?= h($sort) ?>">
    <input type="hidden" name="dir" value="<?= h($dir) ?>">
    <button class="btn btn-primary" type="submit">Lọc</button>
    <?php if ($q !== '' || $statusFilter !== ''): ?><a class="btn btn-secondary" href="/leads">Xóa lọc</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th><a href="/leads<?= h(sort_url('id', $sort, $dir)) ?>">ID<?= h(sort_caret('id', $sort, $dir)) ?></a></th>
                <th><a href="/leads<?= h(sort_url('full_name', $sort, $dir)) ?>">Họ tên<?= h(sort_caret('full_name', $sort, $dir)) ?></a></th>
                <th><a href="/leads<?= h(sort_url('email', $sort, $dir)) ?>">Email<?= h(sort_caret('email', $sort, $dir)) ?></a></th>
                <th>SĐT</th>
                <th>Khóa quan tâm</th>
                <th><a href="/leads<?= h(sort_url('care_status', $sort, $dir)) ?>">Chăm sóc<?= h(sort_caret('care_status', $sort, $dir)) ?></a></th>
                <th><a href="/leads<?= h(sort_url('created_at', $sort, $dir)) ?>">Ngày tạo<?= h(sort_caret('created_at', $sort, $dir)) ?></a></th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($leads === []): ?>
            <tr><td colspan="8" class="muted" style="text-align:center;padding:24px">Không có dữ liệu phù hợp.</td></tr>
        <?php else: foreach ($leads as $lead): ?>
            <tr>
                <td><?= h($lead['id']) ?></td>
                <td><?= h($lead['full_name']) ?></td>
                <td><?= h($lead['email']) ?></td>
                <td><?= h($lead['phone']) ?></td>
                <td><?= h($courseLabels[$lead['course_interest']] ?? $lead['course_interest']) ?></td>
                <td><span class="badge badge-<?= h($lead['care_status']) ?>"><?= h($careLabels[$lead['care_status']] ?? $lead['care_status']) ?></span></td>
                <td><?= h($lead['created_at']) ?></td>
                <td class="actions-cell">
                    <a class="btn btn-sm btn-secondary" href="/leads/edit?id=<?= h($lead['id']) ?>">
                        <span class="material-symbols-outlined" style="font-size:14px;">edit</span> Sửa
                    </a>
                    <form class="inline-form" method="post" action="/leads/delete" onsubmit="return confirm('Xóa lead này?')">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= h($lead['id']) ?>">
                        <button class="btn btn-sm btn-danger" type="submit">
                            <span class="material-symbols-outlined" style="font-size:14px;">delete</span> Xóa
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="/leads?<?= h(query_string(['page' => $page - 1])) ?>">
            <span class="material-symbols-outlined" style="font-size:16px; margin-right:4px;">arrow_back</span> Trước
        </a>
    <?php else: ?>
        <span class="disabled">
            <span class="material-symbols-outlined" style="font-size:16px; margin-right:4px;">arrow_back</span> Trước
        </span>
    <?php endif; ?>
    <span class="current">Trang <?= h($page) ?> / <?= h($lastPage) ?></span>
    <?php if ($page < $lastPage): ?>
        <a href="/leads?<?= h(query_string(['page' => $page + 1])) ?>">
            Sau <span class="material-symbols-outlined" style="font-size:16px; margin-left:4px;">arrow_forward</span>
        </a>
    <?php else: ?>
        <span class="disabled">
            Sau <span class="material-symbols-outlined" style="font-size:16px; margin-left:4px;">arrow_forward</span>
        </span>
    <?php endif; ?>
</div>
