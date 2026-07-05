<div class="error-layout">
    <div class="error-grid">
        <div class="error-visual">
            <span class="material-symbols-outlined icon-lg" style="display: block; margin-bottom: var(--space-md); color: var(--danger);">sentiment_very_dissatisfied</span>
            <div class="error-visual-code">404</div>
            <div class="error-info">
                <h2>Trang không tồn tại</h2>
                <p><?= h($message ?? 'Trang bạn yêu cầu không tồn tại hoặc đã được di chuyển. Quay lại và thử lại hoặc liên hệ quản trị viên.') ?></p>
                <div class="error-actions">
                    <a class="btn btn-primary" href="/">
                        <span class="material-symbols-outlined icon-md">home</span> Về trang chủ
                    </a>
                </div>
            </div>
        </div>

        <div class="error-details">
            <div class="bento-status-card">
                <div class="status-header"><span>Điều hướng nhanh</span></div>
                <a class="system-row" href="/leads">
                    <span class="status-label">Lead tư vấn</span>
                    <span class="material-symbols-outlined status-icon icon-sm">arrow_forward</span>
                </a>
                <a class="system-row" href="/payments">
                    <span class="status-label">Thanh toán học phí</span>
                    <span class="material-symbols-outlined status-icon icon-sm">arrow_forward</span>
                </a>
                <a class="system-row" href="/stats">
                    <span class="status-label">Thống kê &amp; báo cáo</span>
                    <span class="material-symbols-outlined status-icon icon-sm">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</div>
