# Mini Training Center CRM — PHP Secure MVC (Lab06 Final)

Ứng dụng quản lý lead tư vấn và thanh toán học phí cho một trung tâm đào tạo nhỏ, viết bằng PHP thuần theo kiến trúc Front Controller và MVC, kết nối MySQL qua PDO với prepared statements cho mọi thao tác dữ liệu.

Đây là bài nộp cho Lab06 Final, biến thể Training Center CRM: quản lý lead tư vấn (Module A), phiếu thanh toán học phí (Module B), cùng một tài khoản nhân sự (`users`) có hai vai trò admin và staff. Ngoài phần bắt buộc của đề, project còn triển khai thêm nhiều tính năng ở mức sản phẩm thực tế: phân quyền theo người phụ trách, nhập/xuất dữ liệu hàng loạt, xóa hàng loạt có transaction, dashboard thống kê kèm bảng xếp hạng nhân viên, và một webhook mô phỏng tiếp nhận lead từ Facebook Messenger.

## Kiến trúc

- **Controller** mỏng: chỉ đọc `$_GET`/`$_POST`, xác thực CSRF và quyền đăng nhập, gọi Service, rồi render View hoặc redirect.
- **Service**: xử lý toàn bộ nghiệp vụ — validate input, kiểm tra trùng lặp, áp dụng luật phân quyền.
- **Repository**: nơi duy nhất chứa SQL, luôn dùng prepared statement, không bao giờ ráp chuỗi từ input.
- **View**: chỉ hiển thị dữ liệu đã được escape, không truy vấn database.

Mọi request đều đi qua `public/index.php` — nơi duy nhất khởi tạo session, gắn header bảo mật, và giao cho `Router` phân phối theo cặp phương thức HTTP + đường dẫn. Router chỉ so khớp chính xác (không hỗ trợ tham số động kiểu `/leads/{id}`), nên thao tác cần id đều truyền qua query string, ví dụ `/leads/edit?id=5`. Sau mỗi POST thành công, ứng dụng luôn redirect (mẫu Post/Redirect/Get) để F5 không tạo dữ liệu trùng.

## Tính năng

### Phần bắt buộc của đề

- Form công khai `/public-leads/create` cho khách đăng ký tư vấn không cần đăng nhập, có honeypot chống bot và rate limit 5 giây giữa hai lần submit.
- Đăng nhập bằng session: mật khẩu băm bcrypt, sinh lại session id sau khi xác thực để chống session fixation, tự đăng xuất sau 15 phút không hoạt động, đăng xuất hủy toàn bộ session.
- Ba bảng `users`, `leads`, `payments` — đều có khóa chính, ràng buộc unique (email, mã phiếu thanh toán), index phục vụ lọc/sắp xếp, cột thời gian tạo/cập nhật.
- Kết nối PDO chuẩn: ném ngoại lệ khi lỗi, trả kết quả dạng mảng kết hợp, tắt giả lập prepared statement để MySQL tách biệt cấu trúc câu lệnh khỏi dữ liệu.
- Hai module CRUD đầy đủ (lead tư vấn, thanh toán học phí): danh sách, tạo, sửa, cập nhật, xóa — mọi thao tác đổi dữ liệu đều dùng POST.
- Danh sách hỗ trợ tìm kiếm, lọc trạng thái, phân trang bằng LIMIT/OFFSET, sắp xếp theo cột; cột và chiều sắp xếp đều đối chiếu whitelist trước khi vào SQL.
- Vi phạm unique constraint được bắt lại và chuyển thành lỗi thân thiện đúng field, không lộ mã lỗi SQL.
- `/health` trả JSON tình trạng ứng dụng/database; có xử lý riêng cho 404 (route không tồn tại) và 405 (sai phương thức).
- Khi tắt debug, lỗi hệ thống chỉ hiển thị thông báo an toàn cho người dùng; chi tiết đầy đủ được ghi vào `storage/logs/app.log`.

### Phần mở rộng

- CSRF token bắt buộc cho toàn bộ form POST trong hệ thống.
- Soft delete qua cột `deleted_at` thay vì xóa hẳn khỏi bảng.
- Ghi nhớ đăng nhập 30 ngày bằng token xoay vòng lưu dưới dạng băm, không lưu mật khẩu vào cookie.
- Đăng ký tài khoản nhân viên mới, admin phê duyệt hoặc từ chối tài khoản đang chờ — kể cả tài khoản tạo qua nút đăng nhập nhanh Google/Facebook (mô phỏng phía server, không gọi OAuth thật), vẫn phải qua đúng bước chờ duyệt.
- Trang thống kê tổng hợp lead và doanh thu, kèm bảng xếp hạng hiệu suất nhân viên chỉ admin nhìn thấy.
- Trang xem nhật ký lỗi hệ thống dành cho admin.
- Lọc danh sách theo khoảng thời gian tạo; xuất CSV giữ nguyên bộ lọc đang áp dụng (có byte-order-mark để Excel hiển thị đúng tiếng Việt); nhập lead hàng loạt từ CSV với báo lỗi chi tiết theo từng dòng.
- Xóa hàng loạt trong một transaction (admin only) — hoặc xóa trọn vẹn, hoặc không dòng nào bị ảnh hưởng nếu có lỗi giữa chừng.
- Phân quyền theo người phụ trách: mỗi lead có thể gán cho một nhân viên; nhân viên chỉ thấy và sửa được lead của mình, cố truy cập lead người khác qua URL nhận lỗi 404 thay vì bị tiết lộ là bản ghi tồn tại.
- Phím tắt Ctrl+K focus nhanh ô tìm kiếm, Esc đóng lớp phủ đang mở, phân trang hiển thị đầy đủ dải số trang.
- Webhook mô phỏng theo đúng chuẩn Facebook Messenger: xác minh challenge, xác thực chữ ký HMAC SHA-256, validate dữ liệu trích xuất bằng đúng bộ quy tắc dùng cho lead nhập tay.

## Cấu trúc thư mục

```
public/
    index.php              entry point duy nhất, khai báo route, header bảo mật, bắt lỗi toàn cục
    assets/                style.css, app.js

config/
    app.php                tên ứng dụng, cờ debug, cấu hình webhook Facebook (mô phỏng)
    database.php           thông tin kết nối MySQL

app/
    Core/
        Database.php               PDO singleton
        Router.php                 định tuyến theo method + path, xử lý 404/405
        DuplicateRecordException.php
    Controllers/
        AuthController.php         login, logout, đăng ký, đăng nhập nhanh Google/Facebook
        AdminController.php        phê duyệt tài khoản, xem nhật ký hệ thống
        HomeController.php         trang chủ kèm số liệu tổng quan
        LeadController.php         CRUD lead, export/import CSV, xóa hàng loạt
        PaymentController.php      CRUD phiếu thanh toán, export CSV, xóa hàng loạt
        SearchController.php       API tìm kiếm nhanh
        StatsController.php        trang thống kê và bảng xếp hạng nhân viên
        WebhookController.php      webhook Facebook Messenger (mô phỏng)
        HealthController.php       kiểm tra tình trạng ứng dụng/CSDL
    Services/
        LeadService.php
        PaymentService.php
        PublicLeadService.php      validate, honeypot, rate limit cho form công khai
    Repositories/
        UserRepository.php
        LeadRepository.php
        PaymentRepository.php
    Support/
        helpers.php            escape output, CSRF, session, phân quyền, flash message, rate limit
        Response.php           render view, redirect, trả JSON/CSV

views/
    layout.php             khung chung: navbar, toast, dark mode
    home.php
    stats.php
    auth/                  login, register
    leads/                 index, create, edit, _form, import
    payments/              index, create, edit, _form
    admin/                 pending-users, logs
    errors/                404, 405, 500
    partials/

database/
    schema.sql             định nghĩa ba bảng users, leads, payments
    seed.sql                dữ liệu mẫu ban đầu
    seed_data.php           script sinh dữ liệu ngẫu nhiên theo tham số dòng lệnh
    migrations/
        001_add_soft_delete.sql
        002_seed_users.sql
        003_add_pending_status.sql
        004_add_assigned_to_leads.sql

storage/
    logs/app.log           log lỗi hệ thống, sinh tự động khi có lỗi
    remember_tokens.json   token đăng nhập ghi nhớ (băm SHA-256), sinh tự động khi dùng

docker-compose.yml         MySQL 8.4 + phpMyAdmin, tự nạp schema.sql/seed.sql khi khởi tạo lần đầu
```

## Cài đặt và chạy

Yêu cầu PHP 8.1 trở lên với extension `pdo_mysql`, và MySQL 8.0 trở lên.

**1. Chuẩn bị cơ sở dữ liệu**

Cách nhanh nhất là dùng Docker:

```
docker compose up -d
```

Lần khởi tạo đầu tiên, MySQL tự động nạp toàn bộ file trong `database/` theo thứ tự bảng chữ cái, bao gồm `schema.sql` và `seed.sql`. Các migration trong `database/migrations/` cần chạy thủ công theo đúng thứ tự số (001 đến 004) nếu volume dữ liệu đã tồn tại từ trước. PhpMyAdmin đi kèm mở tại `http://localhost:8081`.

Không dùng Docker: tạo database bằng XAMPP/Laragon rồi import lần lượt `database/schema.sql`, `database/seed.sql`, và các file trong `database/migrations/` theo thứ tự số tăng dần.

**2. Cấu hình kết nối**

Sửa `config/database.php` cho khớp môi trường của bạn. Giá trị mặc định (`host=127.0.0.1`, `user=root`, `password=root`) khớp sẵn với `docker-compose.yml`.

**3. Chạy server**

```
php -S localhost:8000 -t public
```

Mở `http://localhost:8000`. Truy cập `/health` để xác nhận đã kết nối được database.

**4. Tài khoản demo**

| Email | Mật khẩu | Vai trò |
|---|---|---|
| admin@center.edu.vn | Admin@123 | admin |
| staff@center.edu.vn | Staff@123 | staff |

Nút đăng nhập nhanh Google/Facebook trên trang login là mô phỏng phía server (không gọi OAuth thật): lần bấm đầu tự tạo tài khoản `google.demo@center.edu.vn` hoặc `facebook.demo@center.edu.vn` với vai trò staff, ở trạng thái chờ duyệt cho đến khi admin duyệt tại `/admin/users/pending`.

**5. Sinh thêm dữ liệu mẫu (tùy chọn)**

```
php database/seed_data.php --leads=200 --payments=150
```

Thêm cờ `--clear` nếu muốn xóa dữ liệu lead/thanh toán hiện có trước khi sinh mới.

**6. Chế độ debug**

Cờ `debug` trong `config/app.php` quyết định cách hiển thị lỗi hệ thống: `true` khi phát triển để xem chi tiết lỗi trên trình duyệt, `false` khi triển khai thật để chỉ hiển thị thông báo an toàn còn chi tiết lỗi vẫn ghi vào `storage/logs/app.log`.

## Danh sách route

| Method | Đường dẫn | Controller@Action | Ý nghĩa |
|---|---|---|---|
| GET | `/` | HomeController@index | Trang chủ, hiển thị số liệu tổng quan |
| GET | `/health` | HealthController@index | JSON kiểm tra tình trạng ứng dụng/CSDL |
| GET | `/api/search` | SearchController@api | API tìm kiếm nhanh (lead + thanh toán) |
| GET | `/login` | AuthController@loginView | Form đăng nhập |
| POST | `/login` | AuthController@handleLogin | Xác thực, tạo session |
| POST | `/logout` | AuthController@logout | Hủy session, đăng xuất |
| POST | `/auth/google` | AuthController@loginGoogle | Đăng nhập nhanh Google (mô phỏng) |
| POST | `/auth/facebook` | AuthController@loginFacebook | Đăng nhập nhanh Facebook (mô phỏng) |
| GET | `/register` | AuthController@registerView | Form đăng ký tài khoản nhân viên |
| POST | `/register` | AuthController@handleRegister | Tạo tài khoản, trạng thái chờ duyệt |
| GET | `/stats` | StatsController@index | Trang thống kê, bảng xếp hạng nhân viên |
| GET | `/admin/users/pending` | AdminController@pendingUsers | Danh sách tài khoản chờ duyệt |
| POST | `/admin/users/approve` | AdminController@approve | Duyệt tài khoản |
| POST | `/admin/users/reject` | AdminController@reject | Từ chối và xóa tài khoản chờ duyệt |
| GET | `/admin/logs` | AdminController@logs | Xem nhật ký lỗi hệ thống |
| GET | `/public-leads/create` | PublicLeadController@create | Form công khai đăng ký tư vấn |
| POST | `/public-leads` | PublicLeadController@store | Nhận đăng ký, honeypot + rate limit |
| GET | `/webhooks/facebook` | WebhookController@verify | Xác minh webhook (hub challenge) |
| POST | `/webhooks/facebook` | WebhookController@handle | Nhận lead từ Messenger, xác thực chữ ký HMAC |
| GET | `/leads` | LeadController@index | Danh sách lead: tìm kiếm, lọc, phân trang, sắp xếp |
| GET | `/leads/export` | LeadController@export | Xuất danh sách lead ra CSV |
| GET | `/leads/import` | LeadController@importView | Form nhập lead hàng loạt từ CSV |
| POST | `/leads/import` | LeadController@handleImport | Xử lý nhập CSV |
| GET | `/leads/create` | LeadController@create | Form thêm lead |
| POST | `/leads/store` | LeadController@store | Tạo lead mới |
| GET | `/leads/edit?id=` | LeadController@edit | Form sửa lead |
| POST | `/leads/update` | LeadController@update | Cập nhật lead |
| POST | `/leads/delete` | LeadController@delete | Xóa mềm một lead |
| POST | `/leads/bulk-delete` | LeadController@bulkDelete | Xóa mềm nhiều lead trong một transaction |
| GET | `/payments` | PaymentController@index | Danh sách phiếu thanh toán |
| GET | `/payments/export` | PaymentController@export | Xuất danh sách thanh toán ra CSV |
| GET | `/payments/create` | PaymentController@create | Form thêm phiếu thanh toán |
| POST | `/payments/store` | PaymentController@store | Tạo phiếu thanh toán mới |
| GET | `/payments/edit?id=` | PaymentController@edit | Form sửa phiếu thanh toán |
| POST | `/payments/update` | PaymentController@update | Cập nhật phiếu thanh toán |
| POST | `/payments/delete` | PaymentController@delete | Xóa mềm một phiếu thanh toán |
| POST | `/payments/bulk-delete` | PaymentController@bulkDelete | Xóa mềm nhiều phiếu thanh toán |
| ANY | đường dẫn không khai báo | Router | Trả 404 |
| — | route tồn tại nhưng sai method | Router | Trả 405 |

Webhook Facebook Messenger dùng secret cấu hình sẵn trong `config/app.php` (`fb_verify_token`, `fb_app_secret`) — đây là giá trị demo cho mục đích kiểm thử, cần thay bằng secret thật nếu tích hợp với một ứng dụng Facebook thật.

## Ghi chú thêm

Báo cáo đầy đủ kèm ảnh chụp minh chứng cho từng yêu cầu, bảng test case và phần trả lời Problem Solving nằm trong `report_lab06.tex` ở thư mục gốc của project.
