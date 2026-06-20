<?php
/** Form dùng chung cho Create & Edit phiếu thanh toán.
 * @var string $action @var string $submitLabel
 * @var array $old @var array $errors @var array $statusLabels @var int $id
 */
$id = $id ?? 0;
?>
<form class="card lead-form-card" method="post" action="<?= h($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <?php if (!empty($id)): ?><input type="hidden" name="id" value="<?= h($id) ?>"><?php endif; ?>

    <div class="form-group">
        <label>Mã thanh toán * <span class="muted">(không trùng)</span></label>
        <input type="text" name="payment_code" value="<?= h($old['payment_code'] ?? '') ?>" class="<?= isset($errors['payment_code']) ? 'input-error' : '' ?>" placeholder="VD: HP-2026-0001">
        <?php if (isset($errors['payment_code'])): ?><p class="field-error"><?= h($errors['payment_code']) ?></p><?php endif; ?>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Tên học viên *</label>
            <input type="text" name="student_name" value="<?= h($old['student_name'] ?? '') ?>" class="<?= isset($errors['student_name']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['student_name'])): ?><p class="field-error"><?= h($errors['student_name']) ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Email học viên</label>
            <input type="text" name="student_email" value="<?= h($old['student_email'] ?? '') ?>" class="<?= isset($errors['student_email']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['student_email'])): ?><p class="field-error"><?= h($errors['student_email']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Khóa học *</label>
            <input type="text" name="course_name" value="<?= h($old['course_name'] ?? '') ?>" class="<?= isset($errors['course_name']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['course_name'])): ?><p class="field-error"><?= h($errors['course_name']) ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Số tiền *</label>
            <div class="input-group <?= isset($errors['amount']) ? 'input-error' : '' ?>">
                <input type="number" name="amount" min="0" step="1000"
                       value="<?= h($old['amount'] ?? '') ?>"
                       placeholder="0">
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
        <textarea name="note" rows="3"><?= h($old['note'] ?? '') ?></textarea>
        <?php if (isset($errors['note'])): ?><p class="field-error"><?= h($errors['note']) ?></p><?php endif; ?>
    </div>

    <div class="form-row">
        <button class="btn btn-primary" type="submit"><?= h($submitLabel) ?></button>
        <a class="btn btn-secondary" href="/payments">Hủy</a>
    </div>
</form>
