<?php $allowedMethods = $allowedMethods ?? []; ?>
<div class="error-layout">
    <div class="error-grid">
        <div class="error-visual">
            <span class="material-symbols-outlined icon-lg" style="display: block; margin-bottom: var(--space-md); color: var(--warning);">block</span>
            <div class="error-visual-code">405</div>
            <div class="error-info">
                <h2>Phương thức không hợp lệ</h2>
                <p>
                    Phương thức <span class="error-method"><?= h($_SERVER['REQUEST_METHOD'] ?? 'HTTP') ?></span> không được hỗ trợ trên route này.
                    <?php if ($allowedMethods !== []): ?>
                        Vui lòng sử dụng: <strong><?= h(implode(', ', $allowedMethods)) ?></strong>.
                    <?php endif; ?>
                </p>
                <div class="error-actions">
                    <a class="btn btn-primary" href="/">
                        <span class="material-symbols-outlined icon-md">home</span> Về trang chủ
                    </a>
                </div>
            </div>
        </div>

        <div class="error-details">
            <div class="bento-status-card">
                <div class="status-header"><span>Phương thức được hỗ trợ</span></div>
                <?php if ($allowedMethods !== []): ?>
                    <?php foreach ($allowedMethods as $method): ?>
                    <div class="system-row">
                        <span class="status-label"><?= h($method) ?></span>
                        <span class="material-symbols-outlined status-icon icon-sm">check_circle</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="system-row">
                        <span class="status-label">Không xác định được</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
