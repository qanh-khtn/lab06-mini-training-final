<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\DuplicateRecordException;
use App\Repositories\UserRepository;
use App\Support\Response;
use Throwable;

class AuthController
{
    // ── Login ─────────────────────────────────────────────────────

    public function loginView(): void
    {
        if (is_logged_in()) {
            redirect('/');
        }

        Response::view('auth/login', [
            'title'  => 'Đăng nhập',
            'errors' => [],
            'old'    => ['email' => ''],
        ]);
    }

    public function handleLogin(): void
    {
        csrf_verify();

        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $remember = isset($_POST['remember_me']);

        $user = $this->findUser($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            Response::view('auth/login', [
                'title'  => 'Đăng nhập',
                'errors' => ['login' => 'Email hoặc mật khẩu không chính xác.'],
                'old'    => ['email' => $email],
            ], 422);
        }

        $this->checkAccountStatus($user, $email);

        $this->establishSession($user);

        if ($remember) {
            set_remember_token($user);
        }

        flash_set('success', 'Đăng nhập thành công. Xin chào, ' . $user['name'] . '!');
        redirect('/');
    }

    // ── Social login (mock) ──────────────────────────────────────────
    // Mô phỏng luồng OAuth cho mục đích demo (không gọi API Google/Facebook
    // thật). Mỗi nhà cung cấp ánh xạ tới một tài khoản demo cố định. QUAN
    // TRỌNG: tài khoản mới tạo qua social vẫn ở trạng thái 'pending' giống
    // hệt đăng ký thủ công (UserRepository::create() mặc định 'pending') và
    // đi qua đúng checkAccountStatus() dùng chung với handleLogin() — không
    // có ngoại lệ bypass phê duyệt nào cho luồng social. Việc xác thực email
    // qua nhà cung cấp (trong OAuth thật) không đồng nghĩa với việc được cấp
    // quyền truy cập hệ thống; quyền đó chỉ do admin quyết định qua
    // /admin/users/pending, không phân biệt tài khoản được tạo bằng cách nào.

    public function loginGoogle(): void
    {
        $this->socialLogin('google', 'google.demo@center.edu.vn', 'Người dùng Google Demo');
    }

    public function loginFacebook(): void
    {
        $this->socialLogin('facebook', 'facebook.demo@center.edu.vn', 'Người dùng Facebook Demo');
    }

    private function socialLogin(string $provider, string $email, string $name): void
    {
        if (is_logged_in()) {
            redirect('/');
        }

        csrf_verify();

        $user = (new UserRepository(Database::connection()))->findOrCreate($name, $email);

        $this->checkAccountStatus($user, $email);

        $this->establishSession($user);

        $providerLabel = $provider === 'google' ? 'Google' : 'Facebook';
        flash_set('success', "Đăng nhập bằng {$providerLabel} thành công. Xin chào, {$user['name']}!");
        redirect('/');
    }

    /**
     * Cổng kiểm tra trạng thái tài khoản dùng chung cho MỌI luồng đăng nhập
     * (password lẫn social) — đảm bảo không có đường tắt nào bỏ qua phê duyệt.
     */
    private function checkAccountStatus(array $user, string $email): void
    {
        if (($user['status'] ?? 'active') === 'pending') {
            Response::view('auth/login', [
                'title'  => 'Đăng nhập',
                'errors' => ['login' => 'Tài khoản của bạn đang chờ duyệt từ admin. Vui lòng quay lại sau.'],
                'old'    => ['email' => $email],
            ], 403);
        }

        if (($user['status'] ?? 'active') !== 'active') {
            Response::view('auth/login', [
                'title'  => 'Đăng nhập',
                'errors' => ['login' => 'Tài khoản này đã bị vô hiệu hóa.'],
                'old'    => ['email' => $email],
            ], 403);
        }
    }

    private function establishSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']          = (string) $user['id'];
        $_SESSION['user_email']       = $user['email'];
        $_SESSION['user_name']        = $user['name'];
        $_SESSION['user_role']        = $user['role'];
        $_SESSION['last_activity_at'] = time();
    }

    // ── Register ───────────────────────────────────────────────────

    public function registerView(): void
    {
        if (is_logged_in()) {
            redirect('/');
        }

        Response::view('auth/register', [
            'title'  => 'Đăng ký tài khoản',
            'errors' => [],
            'old'    => ['name' => '', 'email' => ''],
        ]);
    }

    public function handleRegister(): void
    {
        if (is_logged_in()) {
            redirect('/');
        }

        csrf_verify();

        $name            = trim((string) ($_POST['name'] ?? ''));
        $email           = trim((string) ($_POST['email'] ?? ''));
        $password        = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $errors          = [];

        // Validation
        if ($name === '') {
            $errors['name'] = 'Họ tên không được để trống.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Họ tên tối đa 100 ký tự.';
        }

        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Địa chỉ email không hợp lệ.';
        }

        if ($password === '') {
            $errors['password'] = 'Mật khẩu không được để trống.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Mật khẩu ít nhất 8 ký tự.';
        }

        if ($errors === [] && $password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Mật khẩu xác nhận không khớp.';
        }

        if ($errors !== []) {
            Response::view('auth/register', [
                'title'  => 'Đăng ký tài khoản',
                'errors' => $errors,
                'old'    => ['name' => $name, 'email' => $email],
            ], 422);
        }

        try {
            (new UserRepository(Database::connection()))->create(
                $name,
                $email,
                password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])
            );
        } catch (DuplicateRecordException) {
            Response::view('auth/register', [
                'title'  => 'Đăng ký tài khoản',
                'errors' => ['email' => 'Email này đã được đăng ký.'],
                'old'    => ['name' => $name, 'email' => $email],
            ], 409);
        }

        flash_set('success', 'Đăng ký thành công! Tài khoản của bạn đang chờ admin duyệt. Vui lòng quay lại sau.');
        redirect('/login');
    }

    // ── Logout ─────────────────────────────────────────────────────

    public function logout(): void
    {
        csrf_verify();

        clear_remember_token();
        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('success', 'Bạn đã đăng xuất thành công.');
        redirect('/login');
    }

    // ── Internal helpers ───────────────────────────────────────────

    /**
     * Hardcoded demo accounts take priority (identical to original behavior).
     * Registered accounts are looked up in the database.
     */
    private function findUser(string $email): ?array
    {
        $hardcoded = $this->hardcodedUsers()[$email] ?? null;
        if ($hardcoded !== null) {
            return $hardcoded;
        }

        try {
            return (new UserRepository(Database::connection()))->findByEmail($email);
        } catch (Throwable) {
            return null;
        }
    }

    private function hardcodedUsers(): array
    {
        return [
            'admin@center.edu.vn' => [
                'id'            => '1',
                'email'         => 'admin@center.edu.vn',
                'name'          => 'Quản trị viên',
                'role'          => 'admin',
                'status'        => 'active',
                'password_hash' => '$2y$12$gfs7SXl9uJ4TC6Hq2YlQdeYWQ6Vld.lZi6azaPa4zvGDGFzN06AYW',
            ],
            'staff@center.edu.vn' => [
                'id'            => '2',
                'email'         => 'staff@center.edu.vn',
                'name'          => 'Tư vấn viên',
                'role'          => 'staff',
                'status'        => 'active',
                'password_hash' => '$2y$12$09c/N4HwxhTg8bLqAzCdGOltyVZ8egG75eqowGX05qYImnz71tIi6',
            ],
        ];
    }
}
