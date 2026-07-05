<?php
$errors = $errors ?? [];
$old    = $old ?? ['email' => ''];
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

        <!-- Auth Card -->
        <div class="auth-card">
            <h2>Đăng nhập tài khoản</h2>
            <form method="POST" action="/login" novalidate>
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                <?php if (!empty($errors['login'])): ?>
                    <div class="alert alert-danger"><?= h($errors['login']) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="icon-input-group">
                        <span class="material-symbols-outlined">mail</span>
                        <input id="email" type="email" name="email"
                               value="<?= h($old['email'] ?? '') ?>"
                               autocomplete="username" placeholder="admin@center.edu.vn">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <div class="icon-input-group pwd-wrap">
                        <span class="material-symbols-outlined">lock</span>
                        <input id="password" type="password" name="password"
                               autocomplete="current-password" placeholder="••••••••">
                        <button type="button" class="pwd-toggle" tabindex="-1" title="Hiện/ẩn mật khẩu">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="remember-me-row">
                    <input type="checkbox" id="remember_me" name="remember_me" value="1">
                    <label for="remember_me" class="text-2" style="margin:0;font-weight:400;">Nhớ tôi trong 30 ngày</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Đăng nhập</button>
            </form>

            <div class="social-divider">
                <span>Hoặc đăng nhập bằng</span>
            </div>

            <div class="social-grid">
                <form method="POST" action="/auth/google">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <button class="btn-social" type="submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22c-.22-.67-.35-1.37-.35-2.09z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Google</span>
                    </button>
                </form>
                <form method="POST" action="/auth/facebook">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <button class="btn-social" type="submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047v-2.66c0-3.025 1.792-4.697 4.533-4.697 1.313 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.928-1.956 1.879v2.271h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                        <span>Facebook</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="auth-redirect">
            Chưa có tài khoản?
            <a href="/register">Đăng ký ngay</a>
        </div>
    </div>
</div>
