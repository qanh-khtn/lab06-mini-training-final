<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Support\Response;
use PDO;

class StatsController
{
    public function index(): void
    {
        require_login();

        $pdo = Database::connection();

        // ── Summary numbers ───────────────────────────────────────
        $summary = $pdo->query(
            'SELECT
               (SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL)                           AS total_leads,
               (SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL AND care_status="enrolled") AS enrolled,
               (SELECT COUNT(*) FROM payments WHERE deleted_at IS NULL)                        AS total_payments,
               (SELECT COALESCE(SUM(amount),0) FROM payments WHERE deleted_at IS NULL AND status="paid")    AS paid_revenue,
               (SELECT COALESCE(SUM(amount),0) FROM payments WHERE deleted_at IS NULL AND status="pending") AS pending_revenue'
        )->fetch(PDO::FETCH_ASSOC);

        // ── Lead distribution by care_status ─────────────────────
        $rows = $pdo->query(
            'SELECT care_status, COUNT(*) AS cnt
             FROM leads WHERE deleted_at IS NULL
             GROUP BY care_status ORDER BY cnt DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
        $leadsByStatus = array_column($rows, 'cnt', 'care_status');

        // ── Lead distribution by course ───────────────────────────
        $rows = $pdo->query(
            'SELECT course_interest, COUNT(*) AS cnt
             FROM leads WHERE deleted_at IS NULL
             GROUP BY course_interest ORDER BY cnt DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
        $leadsByCourse = array_column($rows, 'cnt', 'course_interest');

        // ── Payment distribution by status ────────────────────────
        $rows = $pdo->query(
            'SELECT status, COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total
             FROM payments WHERE deleted_at IS NULL
             GROUP BY status ORDER BY cnt DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
        $paymentsByStatus = [];
        foreach ($rows as $r) {
            $paymentsByStatus[$r['status']] = ['cnt' => (int) $r['cnt'], 'total' => (float) $r['total']];
        }

        // ── Revenue by course (paid only) ─────────────────────────
        $revenueByCourse = $pdo->query(
            'SELECT course_name, COUNT(*) AS cnt, SUM(amount) AS total
             FROM payments WHERE deleted_at IS NULL AND status="paid"
             GROUP BY course_name ORDER BY total DESC LIMIT 10'
        )->fetchAll(PDO::FETCH_ASSOC);

        // ── Monthly revenue (last 6 months) ───────────────────────
        $monthlyRows = $pdo->query(
            'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month,
                    COUNT(*) AS cnt,
                    COALESCE(SUM(CASE WHEN status="paid" THEN amount ELSE 0 END), 0) AS revenue
             FROM payments
             WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month'
        )->fetchAll(PDO::FETCH_ASSOC);

        // Fill missing months so the bar chart always has 6 columns
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-$i months"));
            $monthly[$key] = ['cnt' => 0, 'revenue' => 0.0];
        }
        foreach ($monthlyRows as $r) {
            $monthly[$r['month']] = ['cnt' => (int) $r['cnt'], 'revenue' => (float) $r['revenue']];
        }

        // ── Recent leads (7 days) ─────────────────────────────────
        $recentLeads = $pdo->query(
            'SELECT full_name, email, care_status, created_at
             FROM leads WHERE deleted_at IS NULL
             ORDER BY created_at DESC LIMIT 6'
        )->fetchAll(PDO::FETCH_ASSOC);

        Response::view('stats', [
            'title'           => 'Thống kê & Báo cáo',
            'summary'         => $summary,
            'leadsByStatus'   => $leadsByStatus,
            'leadsByCourse'   => $leadsByCourse,
            'paymentsByStatus'=> $paymentsByStatus,
            'revenueByCourse' => $revenueByCourse,
            'monthly'         => $monthly,
            'recentLeads'     => $recentLeads,
            'careLabels'      => [
                'new'        => 'Mới',
                'contacted'  => 'Đã liên hệ',
                'consulting' => 'Đang tư vấn',
                'enrolled'   => 'Đã ghi danh',
                'dropped'    => 'Ngừng theo dõi',
            ],
            'courseLabels'    => [
                'web'    => 'Lập trình Web',
                'mobile' => 'Lập trình Mobile',
                'data'   => 'Phân tích dữ liệu',
                'ai'     => 'AI ứng dụng',
                'other'  => 'Khác',
            ],
            'paymentStatusLabels' => [
                'pending'   => 'Chờ thanh toán',
                'paid'      => 'Đã thanh toán',
                'refunded'  => 'Đã hoàn tiền',
                'cancelled' => 'Đã hủy',
            ],
        ]);
    }
}
