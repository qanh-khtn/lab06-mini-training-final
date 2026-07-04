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
                    <img alt="Google" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBQE6IxjdMNg9JD9Ps3zHMOS0RN-aj3OZ24hFXsG8I9PkBpW9jPNiRciTdWDeOO9NlHC7rO-GP-SOL9bZLOq7LFgIJDi3XR7RfopVuprD86snBHHwQJWUYferU-Vl9hp8x8fh8vRqvoQAPgAbUHZeQJJ8z_48B42q8rMrLWdBgWtA5MPsLuhDj8vTYdf7JZIh7qC4zVrqybZsmYpJAmB1dL0VDlOVYquJ4BLpqnmgt_w_lUFgVmZWp0MA4t_C2HeGVYyYk58f3NFZc"/>
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
