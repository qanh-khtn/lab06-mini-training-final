<?php
$errors = $errors ?? [];
$old    = $old ?? ['email' => ''];
?>
<div style="display:flex;justify-content:center;padding:40px 0 60px;">
<div class="card" style="width:100%;max-width:420px;padding:36px 40px;">

    <div style="text-align:center;margin-bottom:28px;">
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:12px;margin:0 auto 16px;"></div>
        <h1 style="font-size:20px;font-weight:700;letter-spacing:-.3px;color:var(--text);">Đăng nhập</h1>
        <p style="font-size:14px;color:var(--text-2);margin-top:4px;">Vui lòng đăng nhập để quản lý dữ liệu.</p>
    </div>

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

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
            <input type="checkbox" id="remember_me" name="remember_me" value="1"
                   style="width:auto;margin:0;accent-color:var(--primary);">
            <label for="remember_me" style="margin:0;font-size:13.5px;font-weight:400;color:var(--text-2);">Nhớ tôi trong 30 ngày</label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;">Đăng nhập</button>
    </form>

    <div class="dev-note" style="margin-top:20px;">
        <strong style="color:var(--text);">Tài khoản demo:</strong><br>
        admin@center.edu.vn / <code>Admin@123</code><br>
        staff@center.edu.vn / <code>Staff@123</code>
    </div>
</div>
</div>
