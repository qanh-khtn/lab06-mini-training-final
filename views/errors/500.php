<?php /** @var string $title */ ?>
<div class="error-layout">
    <div class="error-visual">
        <span class="material-symbols-outlined icon-lg" style="display: block; margin-bottom: var(--space-md); color: var(--danger);">error</span>
        <div class="error-code">500</div>
    </div>
    <div class="error-info">
        <h2 class="error-title">Lỗi hệ thống</h2>
        <p class="error-desc">Xin lỗi, hệ thống chưa thể xử lý yêu cầu của bạn lúc này. Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>
        <div class="dev-note">
            <strong>Ghi chú cho developer:</strong><br>
            Chi tiết lỗi được ghi vào <code>storage/logs/app.log</code>. Giao diện chỉ hiển thị thông báo an toàn (không lộ SQLSTATE).
        </div>
        <a class="btn btn-primary" href="/">
            <span class="material-symbols-outlined icon-md">dashboard</span> Về Dashboard
        </a>
    </div>
</div>
