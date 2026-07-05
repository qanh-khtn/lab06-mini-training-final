<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\LeadRepository;
use App\Repositories\UserRepository;
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

        $assignedToUserId = ($_SESSION['user_role'] ?? '') === 'staff' ? (int)$_SESSION['user_id'] : null;
        $result = $this->service()->paginate($q, $status, $rawPage, $sort, $dir, $dateFrom, $dateTo, $assignedToUserId);

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

        $assignedToUserId = ($_SESSION['user_role'] ?? '') === 'staff' ? (int)$_SESSION['user_id'] : null;
        $rows         = $this->service()->all($q, $status, $sort, $dir, $dateFrom, $dateTo, $assignedToUserId);
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

        $users = [];
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $users = (new UserRepository(Database::connection()))->allActive();
        }

        Response::view('leads/create', [
            'title'        => 'Thêm Lead tư vấn',
            'errors'       => [],
            'old'          => $this->emptyInput(),
            'courseLabels' => $this->courseLabels(),
            'careLabels'   => $this->careLabels(),
            'users'        => $users,
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

        // Bảo vệ tầng Controller: Staff không được sửa lead của người khác (trả về 404)
        if (($_SESSION['user_role'] ?? '') === 'staff' && (int)($lead['assigned_to'] ?? 0) !== (int)$_SESSION['user_id']) {
            Response::notFound('Không tìm thấy lead cần sửa.');
        }

        $users = [];
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $users = (new UserRepository(Database::connection()))->allActive();
        }

        Response::view('leads/edit', [
            'title'        => 'Sửa Lead tư vấn',
            'errors'       => [],
            'old'          => $lead,
            'id'           => $id,
            'courseLabels' => $this->courseLabels(),
            'careLabels'   => $this->careLabels(),
            'users'        => $users,
        ]);
    }

    public function update(): void
    {
        require_login();
        csrf_verify();

        $id    = (int) ($_POST['id'] ?? 0);
        $lead  = $this->service()->find($id);

        if ($lead === null) {
            Response::notFound('Không tìm thấy lead cần cập nhật.');
        }

        // Bảo vệ tầng Controller: Staff không được sửa lead của người khác (trả về 404)
        if (($_SESSION['user_role'] ?? '') === 'staff' && (int)($lead['assigned_to'] ?? 0) !== (int)$_SESSION['user_id']) {
            Response::notFound('Không tìm thấy lead cần cập nhật.');
        }

        $input = $this->input();
        $input['id'] = $id;

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

    public function importView(): void
    {
        require_login();
        Response::view('leads/import', [
            'title' => 'Nhập hàng loạt Lead từ CSV',
            'errors' => [],
            'success' => null,
        ]);
    }

    public function handleImport(): void
    {
        require_login();
        csrf_verify();

        // Ghi chú: import render THẲNG view trong cùng request (không flash_set +
        // redirect) vì flash_set() chỉ nhận string cho $message (strict_types=1) —
        // danh sách lỗi từng dòng là mảng, không thể đi qua cơ chế toast chuẩn.
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Response::view('leads/import', [
                'title' => 'Nhập hàng loạt Lead từ CSV',
                'errors' => ['Vui lòng chọn một tệp CSV hợp lệ.'],
                'success' => null,
            ], 422);
        }

        $filePath = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            Response::view('leads/import', [
                'title' => 'Nhập hàng loạt Lead từ CSV',
                'errors' => ['Không thể mở tệp CSV.'],
                'success' => null,
            ], 422);
        }

        // Bỏ qua BOM nếu có
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rowErrors = [];
        $validItems = [];
        $lineNo = 0;

        $service = $this->service();

        while (($row = fgetcsv($handle, 2000, ',')) !== false) {
            $lineNo++;

            // Bỏ qua dòng trống
            if (array_filter($row) === []) {
                continue;
            }

            // Bỏ qua dòng tiêu đề nếu phát hiện từ khóa tiêu đề ở cột 1
            if ($lineNo === 1 && (
                str_contains(strtolower($row[0]), 'họ tên') ||
                str_contains(strtolower($row[0]), 'name') ||
                str_contains(strtolower($row[1]), 'email')
            )) {
                continue;
            }

            $fullName       = trim($row[0] ?? '');
            $email          = trim($row[1] ?? '');
            $phone          = trim($row[2] ?? '');
            $courseInterest = trim($row[3] ?? '');
            $careStatus     = trim($row[4] ?? '');
            $note           = trim($row[5] ?? '');

            $item = [
                'full_name'       => $fullName,
                'email'           => $email,
                'phone'           => $phone,
                'course_interest' => $this->normalizeCourse($courseInterest),
                'care_status'     => $this->normalizeStatus($careStatus),
                'note'            => $note,
            ];

            if (($_SESSION['user_role'] ?? '') === 'admin') {
                $item['assigned_to'] = null;
            } else {
                $item['assigned_to'] = (int)$_SESSION['user_id'];
            }

            // Validate bằng đúng logic LeadService::validate()
            $errors = $service->validate($item);
            if ($errors !== []) {
                $errMsgs = [];
                foreach ($errors as $field => $msg) {
                    $errMsgs[] = $msg;
                }
                $rowErrors[] = "Dòng " . $lineNo . " (" . ($fullName ?: 'Chưa có tên') . "): " . implode(' ', $errMsgs);
            } else {
                $validItems[$lineNo] = $item;
            }
        }
        fclose($handle);

        $imported = 0;
        if ($validItems !== []) {
            $db = Database::connection();
            $db->beginTransaction();
            try {
                $repo = new LeadRepository($db);
                foreach ($validItems as $originalLineNo => $item) {
                    try {
                        $repo->create($item);
                        $imported++;
                    } catch (\App\Core\DuplicateRecordException $e) {
                        $rowErrors[] = "Dòng " . $originalLineNo . " (" . $item['full_name'] . "): Email '" . $item['email'] . "' đã tồn tại trong hệ thống.";
                    }
                }
                $db->commit();
            } catch (\Throwable $e) {
                $db->rollBack();
                Response::view('leads/import', [
                    'title' => 'Nhập hàng loạt Lead từ CSV',
                    'errors' => ['Lỗi hệ thống khi import: ' . $e->getMessage()],
                    'success' => null,
                ], 500);
            }
        }

        Response::view('leads/import', [
            'title' => 'Nhập hàng loạt Lead từ CSV',
            'errors' => $rowErrors,
            'success' => $imported > 0 ? "Đã nhập thành công {$imported} lead tư vấn." : null,
        ]);
    }

    private function normalizeCourse(string $val): string
    {
        $val = trim(mb_strtolower($val, 'UTF-8'));
        $map = [
            'lập trình web' => 'web',
            'lập trình mobile' => 'mobile',
            'phân tích dữ liệu' => 'data',
            'ai ứng dụng' => 'ai',
            'khác' => 'other',
            'web' => 'web',
            'mobile' => 'mobile',
            'data' => 'data',
            'ai' => 'ai',
            'other' => 'other'
        ];
        return $map[$val] ?? 'other';
    }

    private function normalizeStatus(string $val): string
    {
        $val = trim(mb_strtolower($val, 'UTF-8'));
        $map = [
            'mới' => 'new',
            'đã liên hệ' => 'contacted',
            'đang tư vấn' => 'consulting',
            'đã ghi danh' => 'enrolled',
            'ngừng theo dõi' => 'dropped',
            'new' => 'new',
            'contacted' => 'contacted',
            'consulting' => 'consulting',
            'enrolled' => 'enrolled',
            'dropped' => 'dropped'
        ];
        return $map[$val] ?? 'new';
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
        $users = [];
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $users = (new UserRepository(Database::connection()))->allActive();
        }

        Response::view($view, [
            'title'        => $title,
            'errors'       => $errors,
            'old'          => $old,
            'id'           => (int) ($old['id'] ?? 0),
            'courseLabels' => $this->courseLabels(),
            'careLabels'   => $this->careLabels(),
            'users'        => $users,
        ], $status);
    }

    private function input(): array
    {
        $data = [
            'full_name'       => trim((string) ($_POST['full_name'] ?? '')),
            'email'           => trim((string) ($_POST['email'] ?? '')),
            'phone'           => trim((string) ($_POST['phone'] ?? '')),
            'course_interest' => trim((string) ($_POST['course_interest'] ?? '')),
            'care_status'     => trim((string) ($_POST['care_status'] ?? 'new')),
            'note'            => trim((string) ($_POST['note'] ?? '')),
        ];

        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $data['assigned_to'] = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '' ? (int)$_POST['assigned_to'] : null;
        } else {
            $data['assigned_to'] = (int)($_SESSION['user_id'] ?? 0);
        }

        return $data;
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
