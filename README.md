# Mini Training Center CRM — PHP Database CRUD (Lab05)

Ứng dụng quản lý **lead tư vấn** và **thanh toán học phí** cho một trung tâm đào tạo,
xây dựng theo mô hình Front Controller: **Browser → public/index.php → Router → Controller → Repository → PDO → MySQL → View/Redirect**.

> Nâng cấp từ Lab04 (form/PRG/validation) lên Lab05 (Database CRUD với PDO, Repository, Search/Pagination, Unique & Index).

## Tính năng (Câu 1 Lab05)
- 3 bảng: `users`, `leads` (Module A), `payments` (Module B) — có primary key, unique key, index.
- Kết nối PDO chuẩn: `charset=utf8mb4`, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES=false`.
- Repository gom toàn bộ SQL, dùng prepared statements cho INSERT/SELECT/UPDATE/DELETE.
- CRUD đầy đủ cho 2 module: List, Create, Edit, Update, Delete.
- Search + pagination + sort an toàn (whitelist sort/direction; LIMIT/OFFSET bind INT).
- Unique constraint chặn trùng `email` (lead) và `payment_code` (thanh toán) + báo lỗi thân thiện đúng field.
- PRG pattern sau POST thành công (create/update/delete đều redirect).
- Error view 404 / 405 / 500; production ẩn SQLSTATE, ghi log vào `storage/logs/app.log`.

## Routes
| Method | URL | Controller@Action |
|---|---|---|
| GET | `/` | HomeController@index (dashboard) |
| GET | `/health` | HealthController@index (JSON DB status) |
| GET | `/leads` | LeadController@index (list + search + pagination + sort) |
| GET | `/leads/create` | LeadController@create |
| POST | `/leads/store` | LeadController@store |
| GET | `/leads/edit?id=1` | LeadController@edit |
| POST | `/leads/update` | LeadController@update |
| POST | `/leads/delete` | LeadController@delete |
| GET | `/payments` | PaymentController@index |
| GET | `/payments/create` | PaymentController@create |
| POST | `/payments/store` | PaymentController@store |
| GET | `/payments/edit?id=1` | PaymentController@edit |
| POST | `/payments/update` | PaymentController@update |
| POST | `/payments/delete` | PaymentController@delete |

## Cài đặt & chạy
1. Yêu cầu: PHP 8.1+ (bật extension `pdo_mysql`), MySQL/MariaDB (XAMPP/Laragon).
2. Tạo & nạp database:
   ```sql
   SOURCE database/schema.sql;
   SOURCE database/seed.sql;
   ```
   (hoặc import 2 file này trong phpMyAdmin)
3. Chỉnh `config/database.php` cho khớp host/user/password máy bạn.
4. Chạy:
   ```bash
   php -S localhost:8000 -t public
   ```
5. Mở http://localhost:8000 — kiểm tra http://localhost:8000/health trả `status: ok`.

## Cấu trúc
```
config/        app.php, database.php
app/Core/      Database.php (PDO), Router.php, DuplicateRecordException.php
app/Repositories/  LeadRepository.php, PaymentRepository.php
app/Controllers/   Home, Health, Lead, Payment
app/Support/   helpers.php, Response.php
views/         home, leads/*, payments/*, errors/{404,405,500}, layout
database/      schema.sql, seed.sql
storage/logs/  app.log
public/        index.php (Front Controller), assets/
```
