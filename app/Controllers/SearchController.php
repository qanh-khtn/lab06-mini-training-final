<?php
namespace App\Controllers;

use App\Core\Database;
use App\Repositories\LeadRepository;
use App\Repositories\PaymentRepository;
use App\Support\Response;

class SearchController {
    /**
     * API endpoint: GET /api/search?q=keyword
     * Returns JSON array of matching leads and payments
     */
    public function api() {
        // Require login
        if (!is_logged_in()) {
            Response::json(401, ['error' => 'Unauthorized']);
        }

        $query = $_GET['q'] ?? '';

        if (strlen(trim($query)) < 2) {
            Response::json(200, ['results' => []]);
        }

        $query = trim($query);
        $db = Database::connection();
        $leadRepo = new LeadRepository($db);
        $paymentRepo = new PaymentRepository($db);

        // Search leads by full_name, email, phone
        $leads = $leadRepo->search($query, limit: 5);

        // Search payments by payment_code, student_name
        $payments = $paymentRepo->search($query, limit: 3);

        // Format results for display
        $results = [];

        foreach ($leads as $lead) {
            $results[] = [
                'name' => h($lead['full_name']),
                'email' => h($lead['email']),
                'url' => '/leads?q=' . urlencode($query),
                'type' => 'lead',
            ];
        }

        foreach ($payments as $payment) {
            $results[] = [
                'name' => h($payment['payment_code']),
                'email' => h($payment['student_name']),
                'url' => '/payments?q=' . urlencode($query),
                'type' => 'payment',
            ];
        }

        Response::json(200, ['results' => $results]);
    }
}
