<?php $allowedMethods = $allowedMethods ?? []; ?>
<div class="error-page">
    <div>
        <div class="error-code">405</div>
        <div>
            <h1 class="error-title">Phương thức không hợp lệ</h1>
            <p class="error-desc">
                Phương thức <span class="error-method"><?= h($_SERVER['REQUEST_METHOD'] ?? 'HTTP') ?></span> không được hỗ trợ trên route này.
                <?php if ($allowedMethods !== []): ?>
                    Vui lòng sử dụng: <strong><?= h(implode(', ', $allowedMethods)) ?></strong>.
                <?php endif; ?>
            </p>
            <a class="btn btn-primary" href="/">← Về trang chủ</a>
        </div>
    </div>
</div>
