<?php
/** Form công khai tạo lead — honeypot + rate limit + CSRF.
 * @var array $errors @var array $old @var array $courseLabels
 */
?>
<section class="page-head">
    <div>
        <h1><?= h($title) ?></h1>
        <p class="muted">Điền thông tin dưới đây và chúng tôi sẽ liên hệ trong 24 giờ.</p>
    </div>
    <a class="btn btn-secondary" href="/">← Về trang chủ</a>
</section>

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

<form class="card lead-form-card" method="post" action="/public-leads">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

    <!-- Honeypot field: ẩn bằng CSS off-screen position -->
    <div class="honeypot-trap" style="position: absolute; left: -9999px; top: -9999px;">
        <label for="website">Website:</label>
        <input type="text" id="website" name="website" value="">
    </div>

    <div class="form-group">
        <label>Họ và tên *</label>
        <input type="text" name="full_name" value="<?= h($old['full_name'] ?? '') ?>" class="<?= isset($errors['full_name']) ? 'input-error' : '' ?>">
        <?php if (isset($errors['full_name'])): ?><p class="field-error"><?= h($errors['full_name']) ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label>Email * <span class="muted">(không trùng)</span></label>
        <input type="text" name="email" value="<?= h($old['email'] ?? '') ?>" class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
        <?php if (isset($errors['email'])): ?><p class="field-error"><?= h($errors['email']) ?></p><?php endif; ?>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Số điện thoại <span class="muted">(tùy chọn)</span></label>
            <input type="text" name="phone" value="<?= h($old['phone'] ?? '') ?>" class="<?= isset($errors['phone']) ? 'input-error' : '' ?>">
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
        <textarea name="note" rows="3"><?= h($old['note'] ?? '') ?></textarea>
        <?php if (isset($errors['note'])): ?><p class="field-error"><?= h($errors['note']) ?></p><?php endif; ?>
    </div>

    <div class="form-row">
        <button class="btn btn-primary" type="submit">Đăng ký tư vấn</button>
        <a class="btn btn-secondary" href="/">Hủy</a>
    </div>
</form>
