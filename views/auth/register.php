<?php
$errors = $errors ?? [];
$old    = $old ?? ['name' => '', 'email' => ''];
?>
<div style="display:flex;justify-content:center;padding:40px 0 60px;">
<div class="card" style="width:100%;max-width:460px;padding:36px 40px;">

    <div style="text-align:center;margin-bottom:28px;">
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:12px;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
            </svg>
        </div>
        <h1 style="font-size:20px;font-weight:700;letter-spacing:-.3px;color:var(--text);">Đăng ký tài khoản</h1>
        <p style="font-size:14px;color:var(--text-2);margin-top:4px;">Tạo tài khoản để truy cập hệ thống CRM.</p>
    </div>

    <form method="POST" action="/register" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="form-group">
            <label for="reg-name">Họ và tên *</label>
            <input id="reg-name" type="text" name="name"
                   value="<?= h($old['name'] ?? '') ?>"
                   autocomplete="name" placeholder="Nguyễn Văn A"
                   class="<?= isset($errors['name']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['name'])): ?><p class="field-error"><?= h($errors['name']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="reg-email">Email *</label>
            <input id="reg-email" type="email" name="email"
                   value="<?= h($old['email'] ?? '') ?>"
                   autocomplete="username" placeholder="ten@example.com"
                   class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['email'])): ?><p class="field-error"><?= h($errors['email']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="reg-password">Mật khẩu * <span class="muted" style="font-size:12px;">(ít nhất 8 ký tự)</span></label>
            <div class="pwd-wrap">
                <input id="reg-password" type="password" name="password"
                       autocomplete="new-password" placeholder="••••••••"
                       class="<?= isset($errors['password']) ? 'input-error' : '' ?>">
                <button type="button" class="pwd-toggle" onclick="togglePwd('reg-password','ico-e2','ico-h2')" tabindex="-1" title="Hiện/ẩn mật khẩu">
                    <svg id="ico-e2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg id="ico-h2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            <?php if (isset($errors['password'])): ?><p class="field-error"><?= h($errors['password']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="reg-confirm">Xác nhận mật khẩu *</label>
            <div class="pwd-wrap">
                <input id="reg-confirm" type="password" name="password_confirm"
                       autocomplete="new-password" placeholder="••••••••"
                       class="<?= isset($errors['password_confirm']) ? 'input-error' : '' ?>">
                <button type="button" class="pwd-toggle" onclick="togglePwd('reg-confirm','ico-e3','ico-h3')" tabindex="-1" title="Hiện/ẩn mật khẩu">
                    <svg id="ico-e3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg id="ico-h3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            <?php if (isset($errors['password_confirm'])): ?><p class="field-error"><?= h($errors['password_confirm']) ?></p><?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;margin-top:4px;">Tạo tài khoản</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:14px;color:var(--text-2);">
        Đã có tài khoản?
        <a href="/login" style="font-weight:600;">Đăng nhập</a>
    </p>
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
