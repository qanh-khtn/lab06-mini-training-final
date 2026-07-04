<div class="error-layout">
    <div class="error-visual">
        <span class="material-symbols-outlined icon-lg" style="display: block; margin-bottom: var(--space-md); color: var(--danger);">sentiment_very_dissatisfied</span>
        <div class="error-code">404</div>
    </div>
    <div class="error-info">
        <h2 class="error-title">Trang không tồn tại</h2>
        <p class="error-desc"><?= h($message ?? 'Trang bạn yêu cầu không tồn tại hoặc đã được di chuyển. Quay lại và thử lại hoặc liên hệ quản trị viên.') ?></p>
        <a class="btn btn-primary" href="/">
            <span class="material-symbols-outlined icon-md">home</span> Về trang chủ
        </a>
    </div>
</div>
