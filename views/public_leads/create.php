<?php
/** Form công khai tạo lead — honeypot + rate limit + CSRF.
 * @var array $errors @var array $old @var array $courseLabels
 */
?>
<div class="auth-page">
    <div class="auth-container" style="max-width: 600px;">
        <!-- Logo Header -->
        <div class="auth-logo-header">
            <div class="logo-box">
                <span class="material-symbols-outlined">school</span>
            </div>
            <h1>Mini Training Center</h1>
            <p>Đăng ký tư vấn khóa học chất lượng cao</p>
        </div>

        <?php if (isset($errors['honeypot']) || isset($errors['rate_limit'])): ?>
            <div class="alert alert-danger">
                <?php if (isset($errors['honeypot'])): ?>
                    <p><?= h($errors['honeypot']) ?></p>
                <?php endif; ?>
                <?php if (isset($errors['rate_limit'])): ?>
                    <p><?= h($errors['rate_limit']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <form class="auth-card" method="post" action="/public-leads">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <!-- Honeypot field -->
            <div class="honeypot-trap" style="position: absolute; left: -9999px; top: -9999px;">
                <label for="website">Website:</label>
                <input type="text" id="website" name="website" value="">
            </div>

            <div class="form-group">
                <label>Họ và tên *</label>
                <div class="icon-input-group">
                    <span class="material-symbols-outlined">person</span>
                    <input type="text" name="full_name" value="<?= h($old['full_name'] ?? '') ?>" class="<?= isset($errors['full_name']) ? 'input-error' : '' ?>" placeholder="Nguyễn Văn A">
                </div>
                <?php if (isset($errors['full_name'])): ?><p class="field-error"><?= h($errors['full_name']) ?></p><?php endif; ?>
            </div>

            <div class="form-group">
                <label>Email * <span class="muted">(không trùng)</span></label>
                <div class="icon-input-group">
                    <span class="material-symbols-outlined">mail</span>
                    <input type="text" name="email" value="<?= h($old['email'] ?? '') ?>" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" placeholder="ten@example.com">
                </div>
                <?php if (isset($errors['email'])): ?><p class="field-error"><?= h($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Số điện thoại <span class="muted">(tùy chọn)</span></label>
                    <div class="icon-input-group">
                        <span class="material-symbols-outlined">phone</span>
                        <input type="text" name="phone" value="<?= h($old['phone'] ?? '') ?>" class="<?= isset($errors['phone']) ? 'input-error' : '' ?>" placeholder="0901 234 567">
                    </div>
                    <?php if (isset($errors['phone'])): ?><p class="field-error"><?= h($errors['phone']) ?></p><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Khóa quan tâm</label>
                    <select name="course_interest">
                        <?php foreach ($courseLabels as $key => $label): ?>
                            <option value="<?= h($key) ?>" <?= ($old['course_interest'] ?? 'web') === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Ghi chú <span class="muted">(tùy chọn)</span></label>
                <textarea name="note" rows="3" placeholder="Nhập nhu cầu hoặc lời nhắn của bạn..."><?= h($old['note'] ?? '') ?></textarea>
                <?php if (isset($errors['note'])): ?><p class="field-error"><?= h($errors['note']) ?></p><?php endif; ?>
            </div>

            <div class="form-row" style="margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Đăng ký tư vấn</button>
                <a class="btn btn-secondary" href="/">Về trang chủ</a>
            </div>
        </form>
    </div>
</div>
