<?php /** @var string $title */ ?>
<div class="error-page">
    <div>
        <div class="error-code">500</div>
        <div>
            <h1 class="error-title">Lỗi hệ thống</h1>
            <p class="error-desc">Xin lỗi, hệ thống chưa thể xử lý yêu cầu của bạn lúc này. Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>
            <div class="dev-note">
                <strong>Ghi chú cho developer:</strong><br>
                Chi tiết lỗi được ghi vào <code>storage/logs/app.log</code>. Giao diện chỉ hiển thị thông báo an toàn (không lộ SQLSTATE).
            </div>
            <a class="btn btn-primary" href="/">← Về Dashboard</a>
        </div>
    </div>
</div>
