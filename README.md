# Mini Training Center CRM — PHP Secure MVC (Lab06 Final)

Ứng dụng quản lý lead tư vấn và thanh toán học phí cho một trung tâm đào tạo nhỏ, viết bằng PHP thuần theo kiến trúc Front Controller và MVC (Controller mỏng, Service xử lý nghiệp vụ, Repository xử lý SQL, View chỉ hiển thị), kết nối MySQL qua PDO với prepared statements cho mọi thao tác dữ liệu.

Đây là bài nộp cho Lab06 Final, biến thể Training Center CRM: quản lý lead tư vấn (Module A), phiếu thanh toán học phí (Module B), cùng một tài khoản nhân sự (users) có hai vai trò admin và staff. Ngoài phần bắt buộc của đề, project còn triển khai thêm khá nhiều tính năng ở mức sản phẩm thực tế: phân quyền theo người phụ trách, nhập/xuất dữ liệu hàng loạt, thao tác xóa hàng loạt có transaction, dashboard thống kê kèm bảng xếp hạng nhân viên, và một webhook mô phỏng tiếp nhận lead từ Facebook Messenger.

## Kiến trúc

Mọi request đều đi qua `public/index.php` — nơi duy nhất khởi tạo session, gắn các header bảo mật, và giao cho `Router` phân phối theo cặp phương thức HTTP và đường dẫn. Router chỉ so khớp chính xác (không hỗ trợ tham số động kiểu `/leads/{id}`), nên các thao tác cần id đều truyền qua query string hoặc form field ẩn, ví dụ `/leads/edit?id=5`.

Luồng xử lý một request điển hình: Router gọi đến Controller tương ứng, Controller đọc dữ liệu từ `$_GET`/`$_POST`, xác thực CSRF và quyền đăng nhập rồi giao cho Service xử lý nghiệp vụ (validate, kiểm tra trùng lặp, áp dụng luật phân quyền). Service gọi Repository để đọc/ghi dữ liệu bằng prepared statement, không bao giờ tự ráp chuỗi SQL từ input. Controller nhận kết quả từ Service rồi hoặc render View (GET) hoặc redirect theo mẫu Post/Redirect/Get (sau POST thành công), tránh việc F5 gửi lại request tạo dữ liệu trùng.

## Tính năng

**Phần bắt buộc của đề**

Form công khai `/public-leads/create` cho khách đăng ký tư vấn mà không cần đăng nhập, có honeypot chống bot và rate limit năm giây giữa hai lần submit liên tiếp trong cùng phiên. Đăng nhập bằng session, mật khẩu băm bằng bcrypt, sinh lại session id sau khi xác thực thành công để chống session fixation, tự động đăng xuất sau mười lăm phút không hoạt động, và đăng xuất luôn hủy toàn bộ session thay vì chỉ xóa biến người dùng. Cơ sở dữ liệu gồm ba bảng `users`, `leads`, `payments`, đều có khóa chính, ràng buộc unique trên các trường không được trùng (email, mã phiếu thanh toán), index phục vụ lọc và sắp xếp, cùng các cột thời gian tạo/cập nhật. Kết nối PDO bật chế độ ném ngoại lệ, trả kết quả dạng mảng kết hợp, và tắt giả lập prepared statement để MySQL xử lý tách biệt cấu trúc câu lệnh với dữ liệu.

Hai module nghiệp vụ (lead tư vấn và thanh toán học phí) đều có đầy đủ danh sách, tạo mới, sửa, cập nhật và xóa; các thao tác làm thay đổi dữ liệu luôn dùng POST. Danh sách hỗ trợ tìm kiếm theo từ khóa, lọc theo trạng thái, phân trang bằng LIMIT/OFFSET, và sắp xếp theo cột — cột và chiều sắp xếp đều được đối chiếu với danh sách whitelist trước khi đưa vào câu lệnh SQL, nên không thể lợi dụng tham số URL để chèn SQL nguy hiểm. Vi phạm ràng buộc unique được bắt lại và chuyển thành thông báo lỗi thân thiện đúng field thay vì để lộ mã lỗi SQL. Có trang `/health` trả JSON tình trạng ứng dụng và cơ sở dữ liệu, có xử lý riêng cho lỗi 404 (route không tồn tại) và 405 (route tồn tại nhưng sai phương thức), và khi tắt chế độ debug thì lỗi hệ thống chỉ hiển thị thông điệp an toàn cho người dùng còn chi tiết đầy đủ được ghi vào `storage/logs/app.log`.

**Phần mở rộng**

Toàn bộ form POST trong hệ thống đều yêu cầu CSRF token hợp lệ. Xóa dữ liệu dùng soft delete qua cột `deleted_at` thay vì xóa hẳn khỏi bảng. Có tùy chọn ghi nhớ đăng nhập ba mươi ngày bằng token xoay vòng lưu dưới dạng băm, không lưu mật khẩu vào cookie. Có luồng đăng ký tài khoản nhân viên mới và trang cho admin phê duyệt hoặc từ chối tài khoản đang chờ, kể cả tài khoản tạo qua nút đăng nhập nhanh Google hoặc Facebook (mô phỏng hoàn toàn phía server, không gọi OAuth thật) — tài khoản social vẫn phải qua đúng bước chờ duyệt như đăng ký tay. Có trang thống kê tổng hợp số liệu lead và doanh thu, kèm bảng xếp hạng hiệu suất nhân viên chỉ admin nhìn thấy. Có trang xem nhật ký lỗi hệ thống dành cho admin.

Danh sách lead và phiếu thanh toán còn hỗ trợ lọc theo khoảng thời gian tạo, xuất ra file CSV giữ nguyên bộ lọc đang áp dụng (có ghi byte-order-mark để Excel hiển thị đúng tiếng Việt), và nhập lead hàng loạt từ file CSV với báo lỗi chi tiết theo từng dòng. Admin có thể chọn nhiều dòng để xóa cùng lúc trong một transaction, đảm bảo hoặc xóa trọn vẹn hoặc không dòng nào bị ảnh hưởng nếu có lỗi giữa chừng; nút và checkbox xóa hàng loạt chỉ hiển thị với admin và được chặn lại ở phía server nếu staff cố gọi thẳng route. Mỗi lead có thể được admin phân công cho một nhân viên phụ trách cụ thể — nhân viên chỉ nhìn thấy và chỉnh sửa được lead của chính mình, cố tình truy cập lead của người khác qua URL sẽ nhận về lỗi 404 thay vì tiết lộ rằng bản ghi đó tồn tại. Giao diện có thêm phím tắt Ctrl+K để focus nhanh vào ô tìm kiếm, phím Esc để đóng các lớp phủ đang mở, và thanh phân trang hiển thị đầy đủ dải số trang. Cuối cùng, có một webhook mô phỏng theo đúng chuẩn xác thực của Facebook Messenger: xác minh challenge khi đăng ký webhook, xác thực chữ ký HMAC SHA-256 của mọi payload gửi đến, và validate dữ liệu trích xuất được bằng đúng bộ quy tắc dùng cho lead nhập tay trước khi ghi vào cơ sở dữ liệu.

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

**Chuẩn bị cơ sở dữ liệu**

Cách nhanh nhất là dùng Docker: chạy `docker compose up -d` tại thư mục gốc project. Lần khởi tạo đầu tiên, MySQL sẽ tự động nạp toàn bộ file trong `database/` theo thứ tự bảng chữ cái, bao gồm `schema.sql` và `seed.sql`; các migration trong `database/migrations/` cần chạy thủ công theo đúng thứ tự số (001 đến 004) nếu volume dữ liệu đã tồn tại từ trước hoặc khi có thay đổi schema mới hơn seed ban đầu. PhpMyAdmin đi kèm được mở tại `http://localhost:8081` để kiểm tra dữ liệu trực quan.

Nếu không dùng Docker, tạo database bằng XAMPP hoặc Laragon rồi import lần lượt `database/schema.sql`, `database/seed.sql`, và các file trong `database/migrations/` theo thứ tự số tăng dần qua phpMyAdmin hoặc dòng lệnh `mysql`.

**Cấu hình kết nối**

Sửa `config/database.php` cho khớp với môi trường của bạn. Giá trị mặc định (`host=127.0.0.1`, `user=root`, `password=root`) khớp sẵn với cấu hình trong `docker-compose.yml`.

**Chạy server**

```
php -S localhost:8000 -t public
```

Mở trình duyệt vào `http://localhost:8000`. Truy cập `/health` để xác nhận ứng dụng đã kết nối được cơ sở dữ liệu.

**Tài khoản demo**

| Email | Mật khẩu | Vai trò |
|---|---|---|
| admin@center.edu.vn | Admin@123 | admin |
| staff@center.edu.vn | Staff@123 | staff |

Nút đăng nhập nhanh bằng Google hoặc Facebook trên trang đăng nhập là mô phỏng phía server, không gọi dịch vụ OAuth thật; lần bấm đầu tiên sẽ tự tạo tài khoản `google.demo@center.edu.vn` hoặc `facebook.demo@center.edu.vn` với vai trò staff, ở trạng thái chờ duyệt cho đến khi admin phê duyệt tại `/admin/users/pending`.

**Sinh thêm dữ liệu mẫu (tùy chọn)**

```
php database/seed_data.php --leads=200 --payments=150
```

Thêm cờ `--clear` nếu muốn xóa dữ liệu lead/thanh toán hiện có trước khi sinh mới.

**Chế độ debug**

Cờ `debug` trong `config/app.php` quyết định cách hiển thị lỗi hệ thống: bật `true` khi phát triển để xem chi tiết lỗi ngay trên trình duyệt, đổi thành `false` khi triển khai thật để chỉ hiển thị thông báo an toàn cho người dùng còn chi tiết lỗi vẫn được ghi đầy đủ vào `storage/logs/app.log`.

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
