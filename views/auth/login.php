<?php
$errors = $errors ?? [];
$old    = $old ?? ['email' => ''];
?>
<div style="display:flex;justify-content:center;padding:40px 0 60px;">
<div class="card" style="width:100%;max-width:420px;padding:36px 40px;">

    <div style="text-align:center;margin-bottom:28px;">
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:12px;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
            </svg>
        </div>
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
            <div class="pwd-wrap">
                <input id="password" type="password" name="password"
                       autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="pwd-toggle" onclick="togglePwd('password','ico-e1','ico-h1')" tabindex="-1" title="Hiện/ẩn mật khẩu">
                    <svg id="ico-e1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg id="ico-h1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
            <input type="checkbox" id="remember_me" name="remember_me" value="1"
                   style="width:auto;margin:0;accent-color:var(--primary);">
            <label for="remember_me" style="margin:0;font-size:13.5px;font-weight:400;color:var(--text-2);">Nhớ tôi trong 30 ngày</label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;">Đăng nhập</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:14px;color:var(--text-2);">
        Chưa có tài khoản?
        <a href="/register" style="font-weight:600;">Đăng ký ngay</a>
    </p>

    <div class="dev-note" style="margin-top:20px;">
        <strong style="color:var(--text);">Tài khoản demo:</strong><br>
        admin@center.edu.vn / <code>Admin@123</code><br>
        staff@center.edu.vn / <code>Staff@123</code>
    </div>
</div>
</div>
<script>
function togglePwd(inputId, showId, hideId) {
    var inp = document.getElementById(inputId);
    var show = document.getElementById(showId);
    var hide = document.getElementById(hideId);
    if (inp.type === 'password') {
        inp.type = 'text';
        show.style.display = 'none';
        hide.style.display = '';
    } else {
        inp.type = 'password';
        show.style.display = '';
        hide.style.display = 'none';
    }
}
</script>
