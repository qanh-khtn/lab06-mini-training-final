<?php
$errors = $errors ?? [];
$old    = $old ?? ['email' => ''];
?>
<section class="page-head">
    <div>
        <h1>Đăng nhập</h1>
        <p class="muted">Đăng nhập bằng tài khoản admin hoặc staff để quản lý dữ liệu.</p>
    </div>
</section>

<div style="max-width:420px; margin:0 auto;">
    <div class="card" style="padding:32px;">
        <form method="POST" action="/login" novalidate>
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <?php if (!empty($errors['login'])): ?>
                <div class="alert alert-danger" style="margin-bottom:18px;"><?= h($errors['login']) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email"
                       value="<?= h($old['email'] ?? '') ?>"
                       autocomplete="username" placeholder="admin@center.edu.vn">
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input id="password" type="password" name="password"
                       autocomplete="current-password" placeholder="••••••••">
            </div>

            <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="remember_me" name="remember_me" value="1" style="width:auto;margin:0;">
                <label for="remember_me" style="margin:0;font-weight:400;">Nhớ tôi trong 30 ngày</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Đăng nhập</button>
        </form>

        <div class="dev-note" style="margin-top:20px;">
            <strong>Tài khoản demo:</strong><br>
            admin@center.edu.vn / <code>Admin@123</code><br>
            staff@center.edu.vn / <code>Staff@123</code>
        </div>
    </div>
</div>
