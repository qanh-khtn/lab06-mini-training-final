<div class="error-page">
    <div>
        <div class="error-code">
            <span class="material-symbols-outlined" style="font-size: 64px; display: block; margin-bottom: 12px; color: var(--danger);">sentiment_very_dissatisfied</span>
            404
        </div>
        <div>
            <h1 class="error-title">Trang không tồn tại</h1>
            <p class="error-desc"><?= h($message ?? 'Trang bạn yêu cầu không tồn tại hoặc đã được di chuyển. Quay lại và thử lại hoặc liên hệ quản trị viên.') ?></p>
            <a class="btn btn-primary" href="/">
                <span class="material-symbols-outlined" style="font-size:16px;">home</span> Về trang chủ
            </a>
        </div>
    </div>
</div>
