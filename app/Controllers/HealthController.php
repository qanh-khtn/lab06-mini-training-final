<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Support\Response;
use Throwable;

class HealthController
{
    public function index(): void
    {
        try {
            Database::connection()->query('SELECT 1');

            Response::json(200, [
                'app'      => 'Mini Training Center CRM',
                'status'   => 'ok',
                'database' => 'connected',
                'time'     => date('c'),
            ]);
        } catch (Throwable $e) {
            error_log('[health] ' . $e->getMessage());

            Response::json(503, [
                'app'      => 'Mini Training Center CRM',
                'status'   => 'error',
                'database' => 'disconnected',
                'time'     => date('c'),
            ]);
        }
    }
}
