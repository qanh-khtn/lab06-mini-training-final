<?php /** @var string $title */ ?>
<div class="error-layout">
    <div class="error-grid">
        <div class="error-visual">
            <span class="material-symbols-outlined icon-lg" style="display: block; margin-bottom: var(--space-md); color: var(--danger);">error</span>
            <div class="error-visual-code">500</div>
            <div class="error-info">
                <h2>Lỗi hệ thống</h2>
                <p>Xin lỗi, hệ thống chưa thể xử lý yêu cầu của bạn lúc này. Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>
                <div class="error-actions">
                    <a class="btn btn-primary" href="/">
                        <span class="material-symbols-outlined icon-md">dashboard</span> Về Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="error-details">
            <div class="bento-status-card">
                <div class="status-header"><span>Trạng thái hệ thống</span></div>
                <div class="system-row">
                    <span class="status-label">Chi tiết lỗi</span>
                    <span class="material-symbols-outlined status-icon icon-sm">description</span>
                </div>
                <div class="system-row">
                    <span class="status-label">Đã ghi vào nhật ký</span>
                    <span class="material-symbols-outlined status-icon icon-sm">check_circle</span>
                </div>
                <div class="system-row">
                    <span class="status-label">Thông báo an toàn (ẩn SQLSTATE)</span>
                    <span class="material-symbols-outlined status-icon icon-sm">check_circle</span>
                </div>
            </div>

            <div class="bento-incident-card">
                <div class="incident-header">
                    <span class="material-symbols-outlined icon-sm">tag</span>
                    <span>Mã tham chiếu sự cố</span>
                </div>
                <div class="incident-code">
                    <span><?= h(date('Y-m-d H:i:s')) ?></span>
                    <span class="material-symbols-outlined icon-sm">content_copy</span>
                </div>
            </div>
        </div>
    </div>
</div>
