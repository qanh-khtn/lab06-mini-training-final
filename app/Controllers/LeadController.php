<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\DuplicateRecordException;
use App\Repositories\LeadRepository;
use App\Support\Response;

/**
 * Module A - Lead tư vấn. Luồng: request -> validate -> repository -> PRG/redirect.
 */
class LeadController
{
    private const COURSE_OPTIONS      = ['web', 'mobile', 'data', 'ai', 'other'];
    private const CARE_STATUS_OPTIONS = ['new', 'contacted', 'consulting', 'enrolled', 'dropped'];
    private const PER_PAGE            = 10;

    public function index(): void
    {
        require_login();

        $q    = trim((string) ($_GET['q'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'id');
        $dir  = (string) ($_GET['dir'] ?? 'asc');
        $rawPage = (int) ($_GET['page'] ?? 1);

        $repo  = $this->repository();
        $total = $repo->countAll($q);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min(max(1, $rawPage), $lastPage);

        // Redirect khi URL có page sai (âm, 0, hoặc vượt lastPage)
        if ($rawPage !== $page) {
            $qs = query_string(['page' => $page]);
            redirect('/leads' . ($qs ? '?' . $qs : ''));
        }

        $offset = ($page - 1) * self::PER_PAGE;

        $leads = $repo->paginate($q, self::PER_PAGE, $offset, $sort, $dir);

        Response::view('leads/index', [
            'title'       => 'Quản lý Lead tư vấn',
            'leads'       => $leads,
            'q'           => $q,
            'sort'        => $sort,
            'dir'         => $dir,
            'page'        => $page,
            'lastPage'    => $lastPage,
            'total'       => $total,
            'courseLabels' => $this->courseLabels(),
            'careLabels'  => $this->careLabels(),
        ]);
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

        $old = $this->input();
        $errors = $this->validate($old);

        if ($errors !== []) {
            $this->renderForm('leads/create', 'Thêm Lead tư vấn', $errors, $old, 422);
        }

        try {
            $this->repository()->create($old);
            flash_set('success', 'Đã thêm lead tư vấn thành công.');
            redirect('/leads');
        } catch (DuplicateRecordException $e) {
            $this->renderForm('leads/create', 'Thêm Lead tư vấn', ['email' => $e->getMessage()], $old, 409);
        }
    }

    public function edit(): void
    {
        require_login();

        $id = (int) ($_GET['id'] ?? 0);
        $lead = $this->repository()->find($id);

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

        $id = (int) ($_POST['id'] ?? 0);
        $repo = $this->repository();

        if ($repo->find($id) === null) {
            Response::notFound('Không tìm thấy lead cần cập nhật.');
        }

        $old = $this->input();
        $old['id'] = $id;
        $errors = $this->validate($old);

        if ($errors !== []) {
            $this->renderForm('leads/edit', 'Sửa Lead tư vấn', $errors, $old, 422);
        }

        try {
            $repo->update($id, $old);
            flash_set('success', 'Đã cập nhật lead tư vấn.');
            redirect('/leads');
        } catch (DuplicateRecordException $e) {
            $this->renderForm('leads/edit', 'Sửa Lead tư vấn', ['email' => $e->getMessage()], $old, 409);
        }
    }

    public function delete(): void
    {
        require_login();
        csrf_verify();

        $id = (int) ($_POST['id'] ?? 0);
        $this->repository()->delete($id);

        flash_set('success', 'Đã xóa lead tư vấn.');
        redirect('/leads');
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

    private function validate(array $input): array
    {
        $errors = [];

        if (($input['full_name'] ?? '') === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên.';
        } elseif ($this->len($input['full_name']) > 100) {
            $errors['full_name'] = 'Họ và tên tối đa 100 ký tự.';
        }

        if (($input['email'] ?? '') === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if (($input['phone'] ?? '') !== '' && !preg_match('/^0[0-9]{9}$/', (string) $input['phone'])) {
            $errors['phone'] = 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.';
        }

        if (!in_array($input['course_interest'] ?? '', self::COURSE_OPTIONS, true)) {
            $errors['course_interest'] = 'Vui lòng chọn khóa học hợp lệ.';
        }

        if (!in_array($input['care_status'] ?? '', self::CARE_STATUS_OPTIONS, true)) {
            $errors['care_status'] = 'Trạng thái chăm sóc không hợp lệ.';
        }

        if ($this->len((string) ($input['note'] ?? '')) > 500) {
            $errors['note'] = 'Ghi chú tối đa 500 ký tự.';
        }

        return $errors;
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

    private function repository(): LeadRepository
    {
        return new LeadRepository(Database::connection());
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

    private function len(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
