<?php
/**
 * View nhập hàng loạt Lead từ CSV.
 * @var string $title
 * @var array $errors
 * @var string|null $success
 */
?>
<section class="page-head">
    <div>
        <h1>Nhập hàng loạt Lead từ CSV</h1>
        <p class="muted">Nhập danh sách lead tư vấn nhanh từ tệp tin CSV.</p>
    </div>
    <a class="btn btn-secondary" href="/leads">← Danh sách</a>
</section>

<div class="grid-2">
    <!-- Form Upload Card -->
    <div class="card">
        <div class="section-head">Chọn tệp CSV từ máy tính</div>
        <form action="/leads/import" method="post" enctype="multipart/form-data" style="margin-top: 16px;">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            
            <div class="form-group">
                <label for="csv_file">Tệp tin CSV *</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required style="padding: 10px; border: 1px dashed var(--border); width: 100%; border-radius: 6px;">
            </div>

            <div style="margin-top: 24px;">
                <button class="btn btn-primary" type="submit">
                    <span class="material-symbols-outlined" style="font-size:18px; vertical-align: middle;">upload</span> Bắt đầu nhập dữ liệu
                </button>
            </div>
        </form>
    </div>

    <!-- Instructions Card -->
    <div class="card">
        <div class="section-head">Định dạng và mẫu tệp tin</div>
        <div style="margin-top: 12px; font-size: 14px; line-height: 1.6;">
            <p>Để hệ thống nhận diện đúng, tệp tin CSV của bạn cần có thứ tự cột như sau:</p>
            <table class="table" style="font-size: 13px; margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Cột</th>
                        <th>Trường dữ liệu</th>
                        <th>Yêu cầu / Ví dụ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>Họ tên *</strong></td>
                        <td>Tối đa 100 ký tự. Ví dụ: Nguyễn Văn A</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>Email *</strong></td>
                        <td>Không trùng lặp. Ví dụ: vana@example.com</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>Số điện thoại</strong></td>
                        <td>10 chữ số, bắt đầu bằng 0. Ví dụ: 0901234567</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><strong>Khóa quan tâm</strong></td>
                        <td><code>Web</code>, <code>Mobile</code>, <code>Data</code>, <code>AI</code>, <code>Other</code></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td><strong>Trạng thái chăm sóc</strong></td>
                        <td><code>Mới</code>, <code>Đã liên hệ</code>, <code>Đang tư vấn</code>, <code>Đã ghi danh</code>, <code>Ngừng theo dõi</code></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td><strong>Ghi chú</strong></td>
                        <td>Tùy chọn. Tối đa 500 ký tự.</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 16px; background: var(--surface-2); padding: 12px; border-radius: 6px;">
                <h4 style="margin: 0 0 6px 0;">Lưu ý quan trọng:</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: var(--muted-clr);">
                    <li>Hệ thống hỗ trợ dòng tiêu đề (Header). Nếu cột đầu tiên là "Họ tên" hoặc "Name", hệ thống sẽ bỏ qua dòng này.</li>
                    <li>Sử dụng bảng mã UTF-8 để không bị lỗi font chữ tiếng Việt.</li>
                    <li>Các dòng không hợp lệ sẽ bị bỏ qua và được báo cáo chi tiết ở bảng kết quả.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Success report -->
<?php if (!empty($success)): ?>
<div class="card" style="border-left: 4px solid var(--success); margin-top: 24px;">
    <div class="section-head" style="color: var(--success); display: flex; align-items: center; gap: 8px;">
        <span class="material-symbols-outlined">check_circle</span> Nhập dữ liệu thành công
    </div>
    <p class="muted" style="margin: 0;"><?= h($success) ?></p>
</div>
<?php endif; ?>

<!-- Detailed errors and reports -->
<?php if (!empty($errors)): ?>
<div class="card" style="border-left: 4px solid var(--danger); margin-top: 24px;">
    <div class="section-head" style="color: var(--danger); display: flex; align-items: center; gap: 8px;">
        <span class="material-symbols-outlined">warning</span> Danh sách dòng dữ liệu bị lỗi
    </div>
    <p class="muted" style="margin-bottom: 12px;">Các dòng này đã bị bỏ qua, vui lòng kiểm tra lại thông tin trong tệp CSV:</p>
    <div style="max-height: 300px; overflow-y: auto;">
        <ul style="padding-left: 20px; color: var(--danger); font-size: 13px; line-height: 1.8; margin: 0;">
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
