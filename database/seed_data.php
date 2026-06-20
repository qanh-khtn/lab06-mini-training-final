<?php
/**
 * Sinh dữ liệu mẫu cho bảng leads và payments.
 * Cách dùng: php database/seed_data.php [--leads=200] [--payments=150] [--clear]
 *   --leads=N    Số lead cần thêm (mặc định 200)
 *   --payments=N Số phiếu thanh toán cần thêm (mặc định 150)
 *   --clear      Xóa tất cả bản ghi hiện có trước khi thêm mới
 */

declare(strict_types=1);

$opts          = getopt('', ['leads:', 'payments:', 'clear']);
$leadsCount    = max(1, (int) ($opts['leads']    ?? 200));
$paymentsCount = max(1, (int) ($opts['payments'] ?? 150));
$clear         = isset($opts['clear']);

// Load database config
$config = require __DIR__ . '/../config/database.php';
$dsn    = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'], $config['port'], $config['database'], $config['charset']
);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Lỗi kết nối DB: {$e->getMessage()}\n");
    exit(1);
}

// ─── Dữ liệu mẫu tiếng Việt ────────────────────────────────────────────────

$lastNames  = ['Nguyễn','Trần','Lê','Phạm','Hoàng','Huỳnh','Phan','Vũ','Đặng','Bùi','Đỗ','Hồ','Ngô','Dương','Lý'];
$midNames   = ['Văn','Thị','Quang','Minh','Thanh','Hồng','Thành','Bảo','Gia','Phúc','Ngọc','Đức','Trọng','Anh','Kim'];
$firstNames = ['An','Bình','Cường','Dũng','Em','Giang','Hoa','Khoa','Lan','Mai','Nam','Oanh','Phong','Quân',
               'Thanh','Thu','Tuấn','Việt','Xuân','Ý','Linh','Tú','Hùng','Hiếu','Thảo','Nhung','Hương','Long','Sơn','Trang'];
$courseKeys   = ['web','mobile','data','ai','other'];
$careStatuses = ['new','contacted','consulting','enrolled','dropped'];

$courseNames = ['Lập trình Web','Lập trình Mobile','Phân tích dữ liệu','AI ứng dụng','AI nâng cao','Thiết kế đồ họa','Tiếng Anh CNTT','Quản trị mạng'];
$amounts     = [3500000, 4000000, 4500000, 4800000, 5000000, 5500000, 6500000, 8000000];
$payStatuses = ['pending','paid','refunded','cancelled'];

// ─── Helpers ────────────────────────────────────────────────────────────────

function rand_name(array $last, array $mid, array $first): string
{
    return $last[array_rand($last)] . ' ' . $mid[array_rand($mid)] . ' ' . $first[array_rand($first)];
}

function rand_phone(): string
{
    $prefixes = ['09','08','07','03','05'];
    return $prefixes[array_rand($prefixes)] . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT);
}

function rand_date(string $start = '-2 years'): string
{
    $ts = rand(strtotime($start), time());
    return date('Y-m-d H:i:s', $ts);
}

// ─── Clear ──────────────────────────────────────────────────────────────────

if ($clear) {
    $pdo->exec('DELETE FROM payments');
    $pdo->exec('DELETE FROM leads');
    $pdo->exec('ALTER TABLE leads AUTO_INCREMENT = 1');
    $pdo->exec('ALTER TABLE payments AUTO_INCREMENT = 1');
    echo "Đã xóa toàn bộ dữ liệu cũ.\n";
}

// ─── Seed Leads ─────────────────────────────────────────────────────────────

$stmtLead = $pdo->prepare(
    'INSERT IGNORE INTO leads (full_name, email, phone, course_interest, care_status, note, created_at)
     VALUES (:full_name, :email, :phone, :course_interest, :care_status, :note, :created_at)'
);

$inserted = 0;
$attempt  = 0;

$pdo->beginTransaction();

while ($inserted < $leadsCount && $attempt < $leadsCount * 3) {
    $attempt++;
    $name  = rand_name($lastNames, $midNames, $firstNames);
    $slug  = strtolower(preg_replace('/\s+/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
    $email = $slug . '.' . rand(100, 9999) . '@example.vn';

    try {
        $stmtLead->execute([
            'full_name'       => $name,
            'email'           => $email,
            'phone'           => rand_phone(),
            'course_interest' => $courseKeys[array_rand($courseKeys)],
            'care_status'     => $careStatuses[array_rand($careStatuses)],
            'note'            => null,
            'created_at'      => rand_date(),
        ]);
        $inserted += $stmtLead->rowCount();
    } catch (PDOException) {
        // email trùng -> bỏ qua
    }
}

$pdo->commit();
echo "Đã thêm {$inserted}/{$leadsCount} leads.\n";

// ─── Seed Payments ──────────────────────────────────────────────────────────

$stmtPay = $pdo->prepare(
    'INSERT IGNORE INTO payments (payment_code, student_name, student_email, course_name, amount, status, note, created_at)
     VALUES (:payment_code, :student_name, :student_email, :course_name, :amount, :status, :note, :created_at)'
);

$payInserted = 0;
$payAttempt  = 0;
$year        = (int) date('Y');

$pdo->beginTransaction();

while ($payInserted < $paymentsCount && $payAttempt < $paymentsCount * 3) {
    $payAttempt++;
    $seq  = rand(1000, 9999);
    $code = "HP-{$year}-{$seq}";
    $name = rand_name($lastNames, $midNames, $firstNames);
    $slug = strtolower(preg_replace('/\s+/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));

    try {
        $stmtPay->execute([
            'payment_code'  => $code,
            'student_name'  => $name,
            'student_email' => $slug . '.' . rand(10, 999) . '@example.vn',
            'course_name'   => $courseNames[array_rand($courseNames)],
            'amount'        => $amounts[array_rand($amounts)],
            'status'        => $payStatuses[array_rand($payStatuses)],
            'note'          => null,
            'created_at'    => rand_date(),
        ]);
        $payInserted += $stmtPay->rowCount();
    } catch (PDOException) {
        // payment_code trùng -> bỏ qua
    }
}

$pdo->commit();
echo "Đã thêm {$payInserted}/{$paymentsCount} payments.\n";
echo "Xong! Kiểm tra tại http://localhost:8000/leads và /payments\n";
