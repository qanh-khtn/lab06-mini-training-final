<?php
/** @var array $users */
?>

<section class="page-head">
    <div>
        <h1>Duyệt tài khoản</h1>
        <p class="muted">Danh sách tài khoản chờ phê duyệt từ nhân viên mới</p>
    </div>
    <a class="btn btn-secondary" href="/">← Trang chủ</a>
</section>

<?php if ($users === []): ?>
    <div class="card" style="text-align:center;padding:40px;">
        <p style="color:var(--text-2);font-size:15px;">Không có tài khoản nào chờ duyệt.</p>
    </div>
<?php else: ?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Role</th>
                <th>Ngày đăng ký</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><strong><?= h($user['name']) ?></strong></td>
                <td><?= h($user['email']) ?></td>
                <td><span class="badge" style="background:var(--info-soft);color:var(--info);border:1px solid #bae6fd;"><?= h($user['role']) ?></span></td>
                <td style="color:var(--text-2);font-size:13px;"><?= h(substr($user['created_at'], 0, 10)) ?></td>
                <td class="actions-cell">
                    <form class="inline-form" method="post" action="/admin/users/approve">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= h($user['id']) ?>">
                        <button class="btn btn-sm btn-success" type="submit">
                            <span class="material-symbols-outlined" style="font-size:14px;">check</span> Phê duyệt
                        </button>
                    </form>
                    <form class="inline-form" method="post" action="/admin/users/reject">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= h($user['id']) ?>">
                        <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Từ chối tài khoản này?')">
                            <span class="material-symbols-outlined" style="font-size:14px;">close</span> Từ chối
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>
