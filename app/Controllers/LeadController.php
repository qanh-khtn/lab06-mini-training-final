<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\LeadRepository;
use App\Services\LeadService;
use App\Support\Response;

/**
 * Module A — Controller mỏng: chỉ đọc HTTP input và gọi redirect/view.
 * Toàn bộ validation + logic nghiệp vụ nằm trong LeadService.
 */
class LeadController
{
    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function index(): void
    {
        require_login();

        $q        = trim((string) ($_GET['q'] ?? ''));
        $status   = trim((string) ($_GET['status'] ?? ''));
        $sort     = (string) ($_GET['sort'] ?? 'id');
        $dir      = (string) ($_GET['dir'] ?? 'asc');
        $rawPage  = (int) ($_GET['page'] ?? 1);
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo   = trim((string) ($_GET['date_to'] ?? ''));

        $result = $this->service()->paginate($q, $status, $rawPage, $sort, $dir, $dateFrom, $dateTo);

        if ($rawPage !== $result['page']) {
            $qs = query_string(['page' => $result['page']]);
            redirect('/leads' . ($qs ? '?' . $qs : ''));
        }

        Response::view('leads/index', [
            'title'        => 'Quản lý Lead tư vấn',
            'leads'        => $result['rows'],
            'q'            => $q,
            'statusFilter' => $status,
            'sort'         => $sort,
            'dir'          => $dir,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'page'         => $result['page'],
            'lastPage'     => $result['lastPage'],
            'total'        => $result['total'],
            'courseLabels' => $this->courseLabels(),
            'careLabels'   => $this->careLabels(),
        ]);
    }

    public function export(): void
    {
        require_login();

        $q        = trim((string) ($_GET['q'] ?? ''));
        $status   = trim((string) ($_GET['status'] ?? ''));
        $sort     = (string) ($_GET['sort'] ?? 'id');
        $dir      = (string) ($_GET['dir'] ?? 'asc');
        $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
        $dateTo   = trim((string) ($_GET['date_to'] ?? ''));

        $rows         = $this->service()->all($q, $status, $sort, $dir, $dateFrom, $dateTo);
        $courseLabels = $this->courseLabels();
        $careLabels   = $this->careLabels();

        $csvRows = array_map(static fn (array $r): array => [
            $r['id'],
            $r['full_name'],
            $r['email'],
            $r['phone'] ?? '',
            $courseLabels[$r['course_interest']] ?? $r['course_interest'],
            $careLabels[$r['care_status']] ?? $r['care_status'],
            $r['created_at'],
        ], $rows);

        Response::csv(
            'leads_' . date('Y-m-d') . '.csv',
            ['ID', 'Họ tên', 'Email', 'Số điện thoại', 'Khóa học quan tâm', 'Trạng thái chăm sóc', 'Ngày tạo'],
            $csvRows
        );
    }

    public function create(): void
    {
        require_login();

        Response::view('leads/create', [
            'title'        => 'Thêm Lead tư vấn',
            'errors'       => [],
            'old'          => $this->emptyInput(),
            'courseLabels' => $this->courseLabels(),
            'careLabels'   => $this->careLabels(),
        ]);
    }

    public function store(): void
    {
        require_login();
        csrf_verify();

        $input  = $this->input();
        $result = $this->service()->create($input);

        if (!$result['ok']) {
            $this->renderForm('leads/create', 'Thêm Lead tư vấn', $result['errors'], $input, $result['duplicate'] ? 409 : 422);
        }

        flash_set('success', 'Đã thêm lead tư vấn thành công.');
        redirect('/leads');
    }

    public function edit(): void
    {
        require_login();

        $id   = (int) ($_GET['id'] ?? 0);
        $lead = $this->service()->find($id);

        if ($lead === null) {
            Response::notFound('Không tìm thấy lead cần sửa.');
        }

        Response::view('leads/edit', [
            'title'        => 'Sửa Lead tư vấn',
            'errors'       => [],
            'old'          => $lead,
            'id'           => $id,
            'courseLabels' => $this->courseLabels(),
            'careLabels'   => $this->careLabels(),
        ]);
    }

    public function update(): void
    {
        require_login();
        csrf_verify();

        $id    = (int) ($_POST['id'] ?? 0);
        $input = $this->input();
        $input['id'] = $id;

        if ($this->service()->find($id) === null) {
            Response::notFound('Không tìm thấy lead cần cập nhật.');
        }

        $result = $this->service()->update($id, $input);

        if (!$result['ok']) {
            $this->renderForm('leads/edit', 'Sửa Lead tư vấn', $result['errors'], $input, $result['duplicate'] ? 409 : 422);
        }

        flash_set('success', 'Đã cập nhật lead tư vấn.');
        redirect('/leads');
    }

    public function delete(): void
    {
        require_admin();
        csrf_verify();

        $id = (int) ($_POST['id'] ?? 0);
        $this->service()->delete($id);

        audit_log('LEAD_DELETE', ['id' => $id, 'by' => $_SESSION['user_email'] ?? 'unknown']);
        flash_set('success', 'Đã xóa lead tư vấn.');
        redirect('/leads');
    }

    public function bulkDelete(): void
    {
        require_admin();
        csrf_verify();

        $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));

        if ($ids === []) {
            flash_set('warning', 'Vui lòng chọn ít nhất một lead để xóa.');
            redirect('/leads');
        }

        $deleted = (new LeadRepository(Database::connection()))->deleteMany($ids);

        audit_log('LEAD_BULK_DELETE', ['ids' => implode(',', $ids), 'by' => $_SESSION['user_email'] ?? 'unknown']);
        flash_set('success', "Đã xóa {$deleted} lead tư vấn.");
        redirect('/leads');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function service(): LeadService
    {
        return new LeadService(new LeadRepository(Database::connection()));
    }

    private function renderForm(string $view, string $title, array $errors, array $old, int $status): void
    {
        Response::view($view, [
            'title'        => $title,
            'errors'       => $errors,
            'old'          => $old,
            'id'           => (int) ($old['id'] ?? 0),
            'courseLabels' => $this->courseLabels(),
            'careLabels'   => $this->careLabels(),
        ], $status);
    }

    private function input(): array
    {
        return [
            'full_name'       => trim((string) ($_POST['full_name'] ?? '')),
            'email'           => trim((string) ($_POST['email'] ?? '')),
            'phone'           => trim((string) ($_POST['phone'] ?? '')),
            'course_interest' => trim((string) ($_POST['course_interest'] ?? '')),
            'care_status'     => trim((string) ($_POST['care_status'] ?? 'new')),
            'note'            => trim((string) ($_POST['note'] ?? '')),
        ];
    }

    private function emptyInput(): array
    {
        return [
            'full_name' => '', 'email' => '', 'phone' => '',
            'course_interest' => 'web', 'care_status' => 'new', 'note' => '',
        ];
    }

    private function courseLabels(): array
    {
        return [
            'web'    => 'Lập trình Web',
            'mobile' => 'Lập trình Mobile',
            'data'   => 'Phân tích dữ liệu',
            'ai'     => 'AI ứng dụng',
            'other'  => 'Khác',
        ];
    }

    private function careLabels(): array
    {
        return [
            'new'        => 'Mới',
            'contacted'  => 'Đã liên hệ',
            'consulting' => 'Đang tư vấn',
            'enrolled'   => 'Đã ghi danh',
            'dropped'    => 'Ngừng theo dõi',
        ];
    }
}
