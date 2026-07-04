<?php $allowedMethods = $allowedMethods ?? []; ?>
<div class="error-layout">
    <div class="error-visual">
        <span class="material-symbols-outlined icon-lg" style="display: block; margin-bottom: var(--space-md); color: var(--warning);">block</span>
        <div class="error-code">405</div>
    </div>
    <div class="error-info">
        <h2 class="error-title">Phương thức không hợp lệ</h2>
        <p class="error-desc">
            Phương thức <code class="error-method"><?= h($_SERVER['REQUEST_METHOD'] ?? 'HTTP') ?></code> không được hỗ trợ trên route này.
            <?php if ($allowedMethods !== []): ?>
                Vui lòng sử dụng: <strong><?= h(implode(', ', $allowedMethods)) ?></strong>.
            <?php endif; ?>
        </p>
        <a class="btn btn-primary" href="/">
            <span class="material-symbols-outlined icon-md">home</span> Về trang chủ
        </a>
    </div>
</div>
