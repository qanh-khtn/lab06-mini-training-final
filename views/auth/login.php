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
                        <button type="button" class="pwd-toggle" onclick="togglePwd()" tabindex="-1" title="Hiện/ẩn mật khẩu">
                            <span class="material-symbols-outlined" id="eye-icon">visibility</span>
                        </button>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
                    <input type="checkbox" id="remember_me" name="remember_me" value="1"
                           style="width:auto;margin:0;accent-color:var(--primary);">
                    <label for="remember_me" style="margin:0;font-weight:400;color:var(--text-2);">Nhớ tôi trong 30 ngày</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Đăng nhập</button>
            </form>

            <div class="social-divider">
                <span>Hoặc đăng nhập bằng</span>
            </div>

            <div class="social-grid">
                <button class="btn-social" type="button">
                    <img alt="Google" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBQE6IxjdMNg9JD9Ps3zHMOS0RN-aj3OZ24hFXsG8I9PkBpW9jPNiRciTdWDeOO9NlHC7rO-GP-SOL9bZLOq7LFgIJDi3XR7RfopVuprD86snBHHwQJWUYferU-Vl9hp8x8fh8vRqvoQAPgAbUHZeQJJ8z_48B42q8rMrLWdBgWtA5MPsLuhDj8vTYdf7JZIh7qC4zVrqybZsmYpJAmB1dL0VDlOVYquJ4BLpqnmgt_w_lUFgVmZWp0MA4t_C2HeGVYyYk58f3NFZc"/>
                    <span>Google</span>
                </button>
                <button class="btn-social" type="button">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047v-2.66c0-3.025 1.792-4.697 4.533-4.697 1.313 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.928-1.956 1.879v2.271h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                    <span>Facebook</span>
                </button>
            </div>
        </div>

        <div class="auth-redirect">
            Chưa có tài khoản?
            <a href="/register">Đăng ký ngay</a>
        </div>

        <div class="dev-note">
            <strong>Tài khoản demo:</strong><br>
            admin@center.edu.vn / <code>Admin@123</code><br>
            staff@center.edu.vn / <code>Staff@123</code>
        </div>
    </div>
</div>
<script>
function togglePwd() {
    var inp = document.getElementById('password');
    var eye = document.getElementById('eye-icon');
    if (inp.type === 'password') {
        inp.type = 'text';
        eye.textContent = 'visibility_off';
    } else {
        inp.type = 'password';
        eye.textContent = 'visibility';
    }
}
</script>
