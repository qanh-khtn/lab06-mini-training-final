<?php
$errors = $errors ?? [];
$old    = $old ?? ['name' => '', 'email' => ''];
?>
<div class="auth-page">
    <div class="auth-container">
        <!-- Logo Header -->
        <div class="auth-logo-header">
            <div class="logo-box">
                <span class="material-symbols-outlined">school</span>
            </div>
            <h1>Mini Training Center</h1>
            <p>Bắt đầu hành trình học tập chuyên nghiệp</p>
        </div>

        <!-- Register Card -->
        <div class="auth-card">
            <h2>Đăng ký tài khoản</h2>
            <form method="POST" action="/register" novalidate>
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                <div class="form-group">
                    <label for="reg-name">Họ và tên *</label>
                    <div class="icon-input-group">
                        <span class="material-symbols-outlined">person</span>
                        <input id="reg-name" type="text" name="name"
                               value="<?= h($old['name'] ?? '') ?>"
                               autocomplete="name" placeholder="Nguyễn Văn A"
                               class="<?= isset($errors['name']) ? 'input-error' : '' ?>">
                    </div>
                    <?php if (isset($errors['name'])): ?><p class="field-error"><?= h($errors['name']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="reg-email">Email *</label>
                    <div class="icon-input-group">
                        <span class="material-symbols-outlined">mail</span>
                        <input id="reg-email" type="email" name="email"
                               value="<?= h($old['email'] ?? '') ?>"
                               autocomplete="username" placeholder="ten@example.com"
                               class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
                    </div>
                    <?php if (isset($errors['email'])): ?><p class="field-error"><?= h($errors['email']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="reg-password">Mật khẩu * <span class="muted" style="font-size:11px;">(ít nhất 8 ký tự)</span></label>
                    <div class="icon-input-group pwd-wrap">
                        <span class="material-symbols-outlined">lock</span>
                        <input id="reg-password" type="password" name="password"
                               autocomplete="new-password" placeholder="••••••••"
                               class="<?= isset($errors['password']) ? 'input-error' : '' ?>">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('reg-password', 'eye-icon-1')" tabindex="-1" title="Hiện/ẩn mật khẩu">
                            <span class="material-symbols-outlined" id="eye-icon-1">visibility</span>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?><p class="field-error"><?= h($errors['password']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="reg-confirm">Xác nhận mật khẩu *</label>
                    <div class="icon-input-group pwd-wrap">
                        <span class="material-symbols-outlined">shield</span>
                        <input id="reg-confirm" type="password" name="password_confirm"
                               autocomplete="new-password" placeholder="••••••••"
                               class="<?= isset($errors['password_confirm']) ? 'input-error' : '' ?>">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('reg-confirm', 'eye-icon-2')" tabindex="-1" title="Hiện/ẩn mật khẩu">
                            <span class="material-symbols-outlined" id="eye-icon-2">visibility</span>
                        </button>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?><p class="field-error"><?= h($errors['password_confirm']) ?></p><?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:4px;">Tạo tài khoản</button>
            </form>

            <div class="social-divider">
                <span>Hoặc đăng ký bằng</span>
            </div>

            <div class="social-grid">
                <button class="btn-social" type="button">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22c-.22-.67-.35-1.37-.35-2.09z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Google</span>
                </button>
                <button class="btn-social" type="button">
                    <span class="material-symbols-outlined" style="color:var(--primary)">face_nod</span>
                    <span>Facebook</span>
                </button>
            </div>
        </div>

        <div class="auth-redirect">
            Đã có tài khoản?
            <a href="/login">Đăng nhập</a>
        </div>
    </div>
</div>
<script>
function togglePwd(inputId, eyeId) {
    var inp = document.getElementById(inputId);
    var eye = document.getElementById(eyeId);
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.textContent = 'visibility_off';
    } else {
        inp.type = 'password';
        eye.textContent = 'visibility';
    }
}
</script>
