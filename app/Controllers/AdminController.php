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

    public function logs(): void
    {
        require_admin();

        $logFile = dirname(__DIR__, 2) . '/storage/logs/app.log';
        $raw     = is_file($logFile) ? file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $raw     = array_reverse($raw);

        $q       = trim($_GET['q'] ?? '');
        $level   = $_GET['level'] ?? '';

        $entries = [];
        foreach ($raw as $line) {
            // Parse: [2024-01-01 12:00:00] message in file:line
            $ts  = '';
            $msg = $line;
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(.+)$/s', $line, $m)) {
                $ts  = $m[1];
                $msg = $m[2];
            }

            // Detect level
            $lvl = 'info';
            if (preg_match('/SQLSTATE|PDOException|Fatal|Uncaught|Error:/i', $msg)) {
                $lvl = 'error';
            } elseif (preg_match('/Warning|Deprecated|Notice/i', $msg)) {
                $lvl = 'warning';
            }

            $entry = ['ts' => $ts, 'msg' => $msg, 'level' => $lvl, 'raw' => $line];

            if ($q !== '' && stripos($line, $q) === false) {
                continue;
            }
            if ($level !== '' && $lvl !== $level) {
                continue;
            }

            $entries[] = $entry;
        }

        Response::view('admin/logs', [
            'title'      => 'Nhật ký hệ thống',
            'entries'    => $entries,
            'totalLines' => count($raw),
            'q'          => $q,
            'level'      => $level,
        ]);
    }
}
