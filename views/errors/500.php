<?php /** @var string $title */ ?>
<section class="error-page">
    <div class="error-code">500</div>
    <h1>Đã có lỗi xảy ra</h1>
    <p class="muted">Xin lỗi, hệ thống chưa thể xử lý yêu cầu của bạn lúc này. Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>
    <div class="dev-note">
        <strong>Ghi chú cho developer:</strong>
        Chi tiết lỗi được ghi vào <code>storage/logs/app.log</code>; giao diện chỉ hiển thị thông báo an toàn (không lộ SQLSTATE).
    </div>
    <a class="btn primary" href="/">Về Dashboard</a>
</section>
