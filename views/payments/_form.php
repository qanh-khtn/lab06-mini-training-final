<?php
/** Form dùng chung cho Create & Edit phiếu thanh toán.
 * @var string $action @var string $submitLabel
 * @var array $old @var array $errors @var array $statusLabels @var array $courseOptions @var int $id
 */
$id            = $id ?? 0;
$courseOptions = $courseOptions ?? [];
?>
<form class="card lead-form-card" method="post" action="<?= h($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <?php if (!empty($id)): ?><input type="hidden" name="id" value="<?= h($id) ?>"><?php endif; ?>

    <div class="form-group">
        <label>Mã thanh toán * <span class="muted">(không trùng)</span></label>
        <div class="icon-input-group">
            <span class="material-symbols-outlined">qr_code</span>
            <input type="text" name="payment_code" value="<?= h($old['payment_code'] ?? '') ?>" class="<?= isset($errors['payment_code']) ? 'input-error' : '' ?>" placeholder="VD: HP-2026-0001">
        </div>
        <?php if (isset($errors['payment_code'])): ?><p class="field-error"><?= h($errors['payment_code']) ?></p><?php endif; ?>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Tên học viên *</label>
            <div class="icon-input-group">
                <span class="material-symbols-outlined">person</span>
                <input type="text" name="student_name" value="<?= h($old['student_name'] ?? '') ?>" class="<?= isset($errors['student_name']) ? 'input-error' : '' ?>" placeholder="Nguyễn Văn A">
            </div>
            <?php if (isset($errors['student_name'])): ?><p class="field-error"><?= h($errors['student_name']) ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Email học viên</label>
            <div class="icon-input-group">
                <span class="material-symbols-outlined">mail</span>
                <input type="text" name="student_email" value="<?= h($old['student_email'] ?? '') ?>" class="<?= isset($errors['student_email']) ? 'input-error' : '' ?>" placeholder="ten@example.com">
            </div>
            <?php if (isset($errors['student_email'])): ?><p class="field-error"><?= h($errors['student_email']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Khóa học *</label>
            <select name="course_name" class="<?= isset($errors['course_name']) ? 'input-error' : '' ?>">
                <option value="">-- Chọn khóa học --</option>
                <?php foreach ($courseOptions as $value => $label): ?>
                    <option value="<?= h($value) ?>" <?= ($old['course_name'] ?? '') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['course_name'])): ?><p class="field-error"><?= h($errors['course_name']) ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Số tiền *</label>
            <div class="input-group <?= isset($errors['amount']) ? 'input-error' : '' ?>">
                <input type="text" inputmode="numeric" id="amount_display"
                       placeholder="0"
                       value="<?= $old['amount'] ?? '' ? number_format((float)($old['amount'] ?? 0), 0, '.', '.') : '' ?>"
                       autocomplete="off">
                <input type="hidden" name="amount" id="amount_raw" value="<?= h($old['amount'] ?? '') ?>">
                <span class="input-addon">đ</span>
            </div>
            <?php if (isset($errors['amount'])): ?><p class="field-error"><?= h($errors['amount']) ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= h($key) ?>" <?= ($old['status'] ?? 'pending') === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Ghi chú</label>
        <textarea name="note" rows="3" placeholder="Nhập ghi chú chi tiết về phiếu thanh toán..."><?= h($old['note'] ?? '') ?></textarea>
        <?php if (isset($errors['note'])): ?><p class="field-error"><?= h($errors['note']) ?></p><?php endif; ?>
    </div>

    <div class="form-row" style="margin-top: 24px;">
        <button class="btn btn-primary" type="submit">
            <span class="material-symbols-outlined" style="font-size:16px;">save</span> <?= h($submitLabel) ?>
        </button>
        <a class="btn btn-secondary" href="/payments">Hủy</a>
    </div>
</form>
<script>
(function () {
    const display = document.getElementById('amount_display');
    const raw     = document.getElementById('amount_raw');
    if (!display || !raw) return;

    function fmt(n) {
        return n === '' ? '' : Number(n.replace(/\./g, '')).toLocaleString('vi-VN');
    }

    display.addEventListener('input', function () {
        const digits = this.value.replace(/[^\d]/g, '');
        this.value = digits ? Number(digits).toLocaleString('vi-VN') : '';
        raw.value  = digits;
    });

    display.addEventListener('blur', function () {
        if (!raw.value) { this.value = ''; }
    });
})();
</script>
