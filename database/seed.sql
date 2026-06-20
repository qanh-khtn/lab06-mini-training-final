-- ============================================================
-- Lab05 - Dữ liệu mẫu (>= 15 bản ghi/module để test pagination)
-- Chạy SAU schema.sql
-- ============================================================
SET NAMES utf8mb4;
SET character_set_client = utf8mb4;

USE training_center_crm;

-- password_hash mẫu (không dùng để đăng nhập trong Lab05 core)
INSERT INTO users (name, email, password_hash, role) VALUES
('Quản trị viên', 'admin@center.edu.vn', '$2y$12$examplehashadminxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'admin'),
('Tư vấn viên',   'staff@center.edu.vn', '$2y$12$examplehashstaffxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'staff');

-- 20 leads tư vấn
INSERT INTO leads (full_name, email, phone, course_interest, care_status, note, created_at) VALUES
('Nguyễn Văn An',   'an.nguyen@example.com',    '0901000001', 'web',    'new',        'Quan tâm khóa Web cơ bản',        '2026-05-01 09:10:00'),
('Trần Thị Bình',   'binh.tran@example.com',    '0901000002', 'mobile', 'contacted',  'Đã gọi tư vấn lần 1',             '2026-05-02 10:20:00'),
('Lê Hoàng Cường',  'cuong.le@example.com',     '0901000003', 'data',   'consulting', 'Đang so sánh học phí',            '2026-05-03 14:05:00'),
('Phạm Thị Dung',   'dung.pham@example.com',    '0901000004', 'ai',     'enrolled',   'Đã ghi danh khóa AI',             '2026-05-04 08:45:00'),
('Hoàng Minh Đức',  'duc.hoang@example.com',    '0901000005', 'web',    'dropped',    'Chưa có nhu cầu',                 '2026-05-05 16:30:00'),
('Vũ Thị Hà',       'ha.vu@example.com',        '0901000006', 'other',  'new',        'Hỏi khóa thiết kế',               '2026-05-06 11:15:00'),
('Đặng Văn Hải',    'hai.dang@example.com',     '0901000007', 'mobile', 'contacted',  'Gửi email tư vấn',                '2026-05-07 09:50:00'),
('Bùi Thị Hoa',     'hoa.bui@example.com',      '0901000008', 'data',   'consulting', 'Muốn học buổi tối',               '2026-05-08 13:25:00'),
('Đỗ Minh Khôi',    'khoi.do@example.com',      '0901000009', 'ai',     'new',        'Quan tâm AI ứng dụng',            '2026-05-09 15:40:00'),
('Ngô Thị Lan',     'lan.ngo@example.com',      '0901000010', 'web',    'enrolled',   'Đã đóng học phí đợt 1',           '2026-05-10 10:05:00'),
('Dương Văn Long',  'long.duong@example.com',   '0901000011', 'mobile', 'contacted',  'Hẹn gọi lại cuối tuần',           '2026-05-11 08:30:00'),
('Lý Thị Mai',      'mai.ly@example.com',       '0901000012', 'data',   'consulting', 'Cần lộ trình chi tiết',           '2026-05-12 14:50:00'),
('Phan Văn Nam',    'nam.phan@example.com',     '0901000013', 'other',  'new',        'Hỏi khóa tiếng Anh CNTT',         '2026-05-13 09:00:00'),
('Trịnh Thị Nga',   'nga.trinh@example.com',    '0901000014', 'web',    'dropped',    'Đã chọn trung tâm khác',          '2026-05-14 16:10:00'),
('Cao Minh Phúc',   'phuc.cao@example.com',     '0901000015', 'ai',     'enrolled',   'Ghi danh khóa AI nâng cao',       '2026-05-15 11:35:00'),
('Hồ Thị Quyên',    'quyen.ho@example.com',     '0901000016', 'mobile', 'contacted',  'Quan tâm Flutter',                '2026-05-16 13:00:00'),
('Tô Văn Sơn',      'son.to@example.com',       '0901000017', 'data',   'new',        'Tư vấn khóa Data Analyst',        '2026-05-17 10:45:00'),
('Mai Thị Thu',     'thu.mai@example.com',      '0901000018', 'web',    'consulting', 'Hỏi trả góp học phí',             '2026-05-18 15:20:00'),
('Lương Văn Tú',    'tu.luong@example.com',     '0901000019', 'other',  'new',        'Quan tâm khóa quản trị mạng',     '2026-05-19 09:30:00'),
('Đoàn Thị Vân',    'van.doan@example.com',     '0901000020', 'ai',     'enrolled',   'Đã ghi danh, chờ khai giảng',     '2026-05-20 14:15:00');

-- 20 phiếu thanh toán học phí
INSERT INTO payments (payment_code, student_name, student_email, course_name, amount, status, note, created_at) VALUES
('HP-2026-0001', 'Nguyễn Văn An',  'an.nguyen@example.com',  'Lập trình Web',       4500000, 'paid',      'Đóng đủ',          '2026-05-02 09:00:00'),
('HP-2026-0002', 'Phạm Thị Dung',  'dung.pham@example.com',  'AI ứng dụng',         6500000, 'paid',      'Đóng đợt 1',       '2026-05-04 10:00:00'),
('HP-2026-0003', 'Ngô Thị Lan',    'lan.ngo@example.com',    'Lập trình Web',       4500000, 'pending',   'Chờ chuyển khoản', '2026-05-10 11:00:00'),
('HP-2026-0004', 'Cao Minh Phúc',  'phuc.cao@example.com',   'AI nâng cao',         8000000, 'paid',      'Đóng đủ',          '2026-05-15 12:00:00'),
('HP-2026-0005', 'Đoàn Thị Vân',   'van.doan@example.com',   'AI ứng dụng',         6500000, 'pending',   'Hẹn đóng tuần sau','2026-05-20 15:00:00'),
('HP-2026-0006', 'Lê Hoàng Cường', 'cuong.le@example.com',   'Phân tích dữ liệu',   5500000, 'cancelled', 'Hủy đăng ký',      '2026-05-06 09:30:00'),
('HP-2026-0007', 'Bùi Thị Hoa',    'hoa.bui@example.com',    'Phân tích dữ liệu',   5500000, 'paid',      'Đóng đủ',          '2026-05-09 14:00:00'),
('HP-2026-0008', 'Trần Thị Bình',  'binh.tran@example.com',  'Lập trình Mobile',    5000000, 'pending',   '',                 '2026-05-11 10:30:00'),
('HP-2026-0009', 'Hồ Thị Quyên',   'quyen.ho@example.com',   'Lập trình Mobile',    5000000, 'paid',      'Đóng đủ',          '2026-05-17 13:30:00'),
('HP-2026-0010', 'Mai Thị Thu',    'thu.mai@example.com',    'Lập trình Web',       4500000, 'pending',   'Trả góp 2 đợt',    '2026-05-19 09:15:00'),
('HP-2026-0011', 'Lý Thị Mai',     'mai.ly@example.com',     'Phân tích dữ liệu',   5500000, 'paid',      'Đóng đủ',          '2026-05-13 16:00:00'),
('HP-2026-0012', 'Tô Văn Sơn',     'son.to@example.com',     'Phân tích dữ liệu',   5500000, 'refunded',  'Hoàn 50%',         '2026-05-18 11:45:00'),
('HP-2026-0013', 'Dương Văn Long', 'long.duong@example.com', 'Lập trình Mobile',    5000000, 'pending',   '',                 '2026-05-12 08:50:00'),
('HP-2026-0014', 'Đặng Văn Hải',   'hai.dang@example.com',   'Lập trình Mobile',    5000000, 'paid',      'Đóng đủ',          '2026-05-08 10:10:00'),
('HP-2026-0015', 'Vũ Thị Hà',      'ha.vu@example.com',      'Thiết kế đồ họa',     4000000, 'pending',   'Chờ xác nhận',     '2026-05-07 15:30:00'),
('HP-2026-0016', 'Đỗ Minh Khôi',   'khoi.do@example.com',    'AI ứng dụng',         6500000, 'paid',      'Đóng đợt 1',       '2026-05-14 09:40:00'),
('HP-2026-0017', 'Phan Văn Nam',   'nam.phan@example.com',   'Tiếng Anh CNTT',      3500000, 'pending',   '',                 '2026-05-16 14:20:00'),
('HP-2026-0018', 'Lương Văn Tú',   'tu.luong@example.com',   'Quản trị mạng',       4800000, 'paid',      'Đóng đủ',          '2026-05-21 10:25:00'),
('HP-2026-0019', 'Hoàng Minh Đức', 'duc.hoang@example.com',  'Lập trình Web',       4500000, 'cancelled', 'Hủy do bận',       '2026-05-22 11:00:00'),
('HP-2026-0020', 'Trịnh Thị Nga',  'nga.trinh@example.com',  'Lập trình Web',       4500000, 'refunded',  'Hoàn toàn bộ',     '2026-05-23 13:10:00');
