<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\LeadRepository;
use App\Repositories\PaymentRepository;
use App\Support\Response;
use Throwable;

class HomeController
{
    public function index(): void
    {
        $leadCount = null;
        $paymentCount = null;
        $dbOk = true;

        // Lấy số liệu nhanh; nếu DB lỗi vẫn render được dashboard (không vỡ trang chủ)
        try {
            $pdo = Database::connection();
            $leadCount = (new LeadRepository($pdo))->countAll();
            $paymentCount = (new PaymentRepository($pdo))->countAll();
        } catch (Throwable $e) {
            $dbOk = false;
        }

        Response::view('home', [
            'title'        => 'Mini Training Center CRM - Dashboard',
            'leadCount'    => $leadCount,
            'paymentCount' => $paymentCount,
            'dbOk'         => $dbOk,
        ]);
    }
}
