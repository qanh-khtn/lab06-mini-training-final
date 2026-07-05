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
                    <label for="reg-password">Mật khẩu * <span class="text-muted">(ít nhất 8 ký tự)</span></label>
                    <div class="icon-input-group pwd-wrap">
                        <span class="material-symbols-outlined">lock</span>
                        <input id="reg-password" type="password" name="password"
                               autocomplete="new-password" placeholder="••••••••"
                               class="<?= isset($errors['password']) ? 'input-error' : '' ?>">
                        <button type="button" class="pwd-toggle" tabindex="-1" title="Hiện/ẩn mật khẩu">
                            <span class="material-symbols-outlined">visibility</span>
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
                        <button type="button" class="pwd-toggle" tabindex="-1" title="Hiện/ẩn mật khẩu">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?><p class="field-error"><?= h($errors['password_confirm']) ?></p><?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:4px;">Tạo tài khoản</button>
            </form>
        </div>

        <div class="auth-redirect">
            Đã có tài khoản?
            <a href="/login">Đăng nhập</a>
        </div>
    </div>
</div>
