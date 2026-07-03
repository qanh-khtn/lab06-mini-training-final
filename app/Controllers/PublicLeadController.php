<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\LeadRepository;
use App\Services\PublicLeadService;
use App\Support\Response;

/**
 * Controller cho form công khai tạo lead (không cần đăng nhập).
 * Controller mỏng: chỉ đọc input, gọi service, render/redirect.
 */
class PublicLeadController
{
    public function create(): void
    {
        Response::view('public_leads/create', [
            'title' => 'Đăng ký tư vấn',
            'errors' => [],
            'old' => $this->emptyInput(),
            'courseLabels' => $this->courseLabels(),
        ]);
    }

    public function store(): void
    {
        csrf_verify();

        $input = $this->input();
        $result = $this->service()->create($input);

        if (!$result['ok']) {
            Response::view('public_leads/create', [
                'title' => 'Đăng ký tư vấn',
                'errors' => $result['errors'],
                'old' => $input,
                'courseLabels' => $this->courseLabels(),
            ], $result['duplicate'] ? 409 : 422);
            return;
        }

        flash_set('success', 'Cảm ơn bạn đã đăng ký tư vấn. Chúng tôi sẽ liên hệ trong 24 giờ.');
        redirect('/public-leads/create');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function service(): PublicLeadService
    {
        return new PublicLeadService(new LeadRepository(Database::connection()));
    }

    private function input(): array
    {
        return [
            'full_name' => trim((string)($_POST['full_name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'course_interest' => trim((string)($_POST['course_interest'] ?? 'web')),
            'note' => trim((string)($_POST['note'] ?? '')),
            'website' => trim((string)($_POST['website'] ?? '')),
        ];
    }

    private function emptyInput(): array
    {
        return [
            'full_name' => '',
            'email' => '',
            'phone' => '',
            'course_interest' => 'web',
            'note' => '',
            'website' => '',
        ];
    }

    private function courseLabels(): array
    {
        return [
            'web' => 'Lập trình Web',
            'mobile' => 'Lập trình Mobile',
            'data' => 'Phân tích dữ liệu',
            'ai' => 'AI ứng dụng',
            'other' => 'Khác',
        ];
    }
}
