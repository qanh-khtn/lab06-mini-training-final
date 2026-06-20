<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;

class AuthController
{
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

        $email    = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $remember = isset($_POST['remember_me']);
        $user     = $this->users()[$email] ?? null;

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::view('auth/login', [
                'title'  => 'Đăng nhập',
                'errors' => ['login' => 'Email hoặc mật khẩu không chính xác.'],
                'old'    => ['email' => $email],
            ], 422);
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['last_activity_at'] = time();

        if ($remember) {
            set_remember_token($user);
        }

        flash_set('success', 'Đăng nhập thành công. Xin chào, ' . $user['name'] . '!');
        redirect('/');
    }

    public function logout(): void
    {
        csrf_verify();

        clear_remember_token();
        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('success', 'Bạn đã đăng xuất thành công.');
        redirect('/login');
    }

    private function users(): array
    {
        return [
            'admin@center.edu.vn' => [
                'id'            => '1',
                'email'         => 'admin@center.edu.vn',
                'name'          => 'Quản trị viên',
                'role'          => 'admin',
                'password_hash' => '$2y$12$gfs7SXl9uJ4TC6Hq2YlQdeYWQ6Vld.lZi6azaPa4zvGDGFzN06AYW',
            ],
            'staff@center.edu.vn' => [
                'id'            => '2',
                'email'         => 'staff@center.edu.vn',
                'name'          => 'Tư vấn viên',
                'role'          => 'staff',
                'password_hash' => '$2y$12$09c/N4HwxhTg8bLqAzCdGOltyVZ8egG75eqowGX05qYImnz71tIi6',
            ],
        ];
    }
}
