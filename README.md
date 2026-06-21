# Mini Training Center CRM — PHP Database CRUD (Lab05)

Ứng dụng quản lý lead tư vấn và thanh toán học phí cho một trung tâm đào tạo nhỏ, xây dựng thuần PHP theo mô hình Front Controller với PDO và MySQL.

Nâng cấp từ Lab04 (form/PRG/validation) lên Lab05 với đầy đủ: Database CRUD, Repository pattern, Service layer, Search/Pagination, Auth, Thống kê và phân quyền admin.

## Tính năng chính

**Module cốt lõi (Lab05 yêu cầu)**

- Ba bảng: `users`, `leads` (Module A), `payments` (Module B) — có primary key, unique key, index.
- Kết nối PDO chuẩn: `charset=utf8mb4`, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES=false`.
- Repository gom toàn bộ SQL, dùng prepared statements cho mọi thao tác INSERT/SELECT/UPDATE/DELETE.
- CRUD đầy đủ cho 2 module: danh sách, tạo mới, sửa, xóa.
- Tìm kiếm kết hợp từ khóa và lọc theo trạng thái; phân trang với LIMIT/OFFSET bind INT; sắp xếp an toàn theo whitelist.
- Unique constraint chặn trùng `email` (lead) và `payment_code` (thanh toán), có thông báo lỗi thân thiện đúng field.
- PRG pattern sau mỗi POST thành công (tạo/sửa/xóa đều redirect).
- Error view 404/405/500; môi trường production ẩn SQLSTATE, ghi log vào `storage/logs/app.log`.

**Phần làm thêm (Bonus)**

- Soft delete qua cột `deleted_at`; mọi truy vấn đọc đều lọc `deleted_at IS NULL`.
- Service layer: `LeadService`, `PaymentService` xử lý validate và nghiệp vụ; controller chỉ nhận/trả dữ liệu.
- Script seed dữ liệu ngẫu nhiên: `php database/seed_data.php --leads=200 --payments=150`.
- Đăng nhập/đăng xuất với session, cookie "Nhớ tôi 30 ngày", timeout session.
- Đăng ký tài khoản nhân viên mới; tài khoản mới tự động ở trạng thái `pending` cho đến khi admin phê duyệt.
- Admin có thể xem danh sách tài khoản chờ duyệt, phê duyệt hoặc từ chối tại `/admin/users/pending`.
- Trang thống kê `/stats`: tổng lead, tỉ lệ ghi danh, doanh thu, phân bổ theo trạng thái, biểu đồ doanh thu 6 tháng gần nhất và top khóa học.
- Giao diện thiết kế lại hoàn toàn: design token CSS, dark mode, hiệu ứng chuyển động mượt, responsive.
- Nút ẩn/hiện mật khẩu trên form đăng nhập và đăng ký.

## Routes

| Method | URL | Controller |
|--------|-----|------------|
| GET | `/` | HomeController@index |
| GET | `/login` | AuthController@loginView |
| POST | `/login` | AuthController@handleLogin |
| POST | `/logout` | AuthController@logout |
| GET | `/register` | AuthController@registerView |
| POST | `/register` | AuthController@handleRegister |
| GET | `/stats` | StatsController@index |
| GET | `/admin/users/pending` | AdminController@pendingUsers |
| POST | `/admin/users/approve` | AdminController@approve |
| POST | `/admin/users/reject` | AdminController@reject |
| GET | `/leads` | LeadController@index |
| GET | `/leads/create` | LeadController@create |
| POST | `/leads/store` | LeadController@store |
| GET | `/leads/edit?id=` | LeadController@edit |
| POST | `/leads/update` | LeadController@update |
| POST | `/leads/delete` | LeadController@delete |
| GET | `/payments` | PaymentController@index |
| GET | `/payments/create` | PaymentController@create |
| POST | `/payments/store` | PaymentController@store |
| GET | `/payments/edit?id=` | PaymentController@edit |
| POST | `/payments/update` | PaymentController@update |
| POST | `/payments/delete` | PaymentController@delete |

## Cài đặt và chạy

**Yêu cầu:** PHP 8.1 trở lên (bật extension `pdo_mysql`), MySQL 8.0 trở lên (XAMPP, Laragon hoặc Docker).

**Bước 1 — Tạo database**

Import các file SQL theo thứ tự trong phpMyAdmin hoặc qua dòng lệnh:

```sql
SOURCE database/schema.sql;
SOURCE database/migrations/002_seed_users.sql;
```

Nếu database đã tồn tại từ trước và cột `status` của bảng `users` chưa có giá trị `pending`, chạy thêm:

```sql
SOURCE database/migrations/003_add_pending_status.sql;
```

**Bước 2 — Cấu hình kết nối**

Chỉnh thông tin host, user, password trong `config/database.php` cho khớp với máy cục bộ.

**Bước 3 — Khởi động server**

```bash
php -S localhost:8000 -t public
```

Mở trình duyệt vào `http://localhost:8000`. Trang `/health` trả JSON `{ "status": "ok" }` để kiểm tra kết nối database.

**Tài khoản demo**

| Email | Mật khẩu | Vai trò |
|-------|----------|---------|
| admin@center.edu.vn | Admin@123 | admin |
| staff@center.edu.vn | Staff@123 | staff |

**Seed dữ liệu thử nghiệm (tùy chọn)**

```bash
php database/seed_data.php --leads=200 --payments=150
```

## Cấu trúc thư mục

```
config/
    app.php              -- cấu hình chung (debug, timezone)
    database.php         -- thông tin kết nối PDO

app/
    Core/
        Database.php     -- PDO singleton
        Router.php       -- front controller dispatcher
        DuplicateRecordException.php
    Controllers/
        AuthController.php
        AdminController.php
        HomeController.php
        LeadController.php
        PaymentController.php
        StatsController.php
        HealthController.php
    Repositories/
        UserRepository.php
        LeadRepository.php
        PaymentRepository.php
    Services/
        LeadService.php
        PaymentService.php
    Support/
        helpers.php      -- h(), csrf_token(), flash_*, require_login(), require_admin()
        Response.php     -- view(), redirect(), json()

views/
    layout.php           -- navbar, toast, dark mode toggle
    home.php
    stats.php
    auth/
        login.php
        register.php
    leads/               -- index, create, edit, _form
    payments/            -- index, create, edit, _form
    admin/
        pending-users.php
    errors/              -- 404, 405, 500

database/
    schema.sql
    seed.sql
    seed_data.php        -- script sinh dữ liệu ngẫu nhiên
    migrations/
        001_add_soft_delete.sql
        002_seed_users.sql
        003_add_pending_status.sql

public/
    index.php            -- front controller
    assets/
        style.css
        app.js

storage/
    logs/app.log
```
