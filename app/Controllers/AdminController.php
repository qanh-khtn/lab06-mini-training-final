<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\UserRepository;
use App\Support\Response;

class AdminController
{
    public function pendingUsers(): void
    {
        require_admin();

        $users = (new UserRepository(Database::connection()))->findPending();

        Response::view('admin/pending-users', [
            'title' => 'Duyệt tài khoản',
            'users' => $users,
        ]);
    }

    public function approve(): void
    {
        require_admin();
        csrf_verify();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            (new UserRepository(Database::connection()))->approve($id);
            flash_set('success', 'Đã phê duyệt tài khoản.');
        }

        redirect('/admin/users/pending');
    }

    public function reject(): void
    {
        require_admin();
        csrf_verify();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            (new UserRepository(Database::connection()))->reject($id);
            flash_set('success', 'Đã từ chối tài khoản.');
        }

        redirect('/admin/users/pending');
    }
}
