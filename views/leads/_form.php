<?php
/** Form dùng chung cho Create & Edit lead.
 * @var string $action @var string $submitLabel
 * @var array $old @var array $errors @var array $courseLabels @var array $careLabels
 * @var int $id (0 nếu tạo mới)
 */
$id = $id ?? 0;
?>
<form class="card lead-form-card" method="post" action="<?= h($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <?php if (!empty($id)): ?><input type="hidden" name="id" value="<?= h($id) ?>"><?php endif; ?>

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
            <label>Số điện thoại</label>
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
                    <option value="<?= h($key) ?>" <?= ($old['course_interest'] ?? '') === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Trạng thái chăm sóc</label>
            <select name="care_status">
                <?php foreach ($careLabels as $key => $label): ?>
                    <option value="<?= h($key) ?>" <?= ($old['care_status'] ?? 'new') === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
    <div class="form-group">
        <label>Nhân viên phụ trách</label>
        <select name="assigned_to">
            <option value="">-- Chưa phân công --</option>
            <?php foreach ($users ?? [] as $user): ?>
                <option value="<?= h($user['id']) ?>" <?= ((int)($old['assigned_to'] ?? 0) === (int)$user['id']) ? 'selected' : '' ?>>
                    <?= h($user['name']) ?> (<?= h($user['email']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['assigned_to'])): ?><p class="field-error"><?= h($errors['assigned_to']) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label>Ghi chú</label>
        <textarea name="note" rows="3" placeholder="Nhập ghi chú chi tiết về nhu cầu khách hàng..."><?= h($old['note'] ?? '') ?></textarea>
        <?php if (isset($errors['note'])): ?><p class="field-error"><?= h($errors['note']) ?></p><?php endif; ?>
    </div>

    <div class="form-row" style="margin-top: 24px;">
        <button class="btn btn-primary" type="submit">
            <span class="material-symbols-outlined" style="font-size:16px;">save</span> <?= h($submitLabel) ?>
        </button>
        <a class="btn btn-secondary" href="/leads">Hủy</a>
    </div>
</form>
