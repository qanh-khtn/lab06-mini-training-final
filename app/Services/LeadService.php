<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DuplicateRecordException;
use App\Repositories\LeadRepository;

/**
 * Tầng Service cho Module A (Lead tư vấn).
 * Controller chỉ đọc HTTP input / gọi redirect — toàn bộ logic nghiệp vụ nằm ở đây.
 */
class LeadService
{
    public const COURSE_OPTIONS      = ['web', 'mobile', 'data', 'ai', 'other'];
    public const CARE_STATUS_OPTIONS = ['new', 'contacted', 'consulting', 'enrolled', 'dropped'];
    public const PER_PAGE            = 10;

    public function __construct(private LeadRepository $repo) {}

    /**
     * Trả về mảng chứa rows, total, page (đã clamp) và lastPage.
     * Controller so sánh rawPage vs page để quyết định có redirect hay không.
     */
    public function paginate(string $q, string $status, int $rawPage, string $sort, string $dir, string $dateFrom = '', string $dateTo = '', ?int $assignedToUserId = null): array
    {
        $dateFrom = $this->validDate($dateFrom);
        $dateTo   = $this->validDate($dateTo);

        $total    = $this->repo->countAll($q, $status, $dateFrom, $dateTo, $assignedToUserId);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min(max(1, $rawPage), $lastPage);
        $offset   = ($page - 1) * self::PER_PAGE;
        $rows     = $this->repo->paginate($q, self::PER_PAGE, $offset, $sort, $dir, $status, $dateFrom, $dateTo, $assignedToUserId);

        return compact('rows', 'total', 'page', 'lastPage');
    }

    /** Trả toàn bộ danh sách khớp filter (không phân trang) — dùng cho export CSV. */
    public function all(string $q, string $status, string $sort, string $dir, string $dateFrom = '', string $dateTo = '', ?int $assignedToUserId = null): array
    {
        return $this->repo->all($q, $status, $sort, $dir, $this->validDate($dateFrom), $this->validDate($dateTo), $assignedToUserId);
    }

    /** Chuẩn hóa ngày dạng YYYY-MM-DD; sai định dạng thì coi như không lọc. */
    private function validDate(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
    }

    public function find(int $id): ?array
    {
        return $this->repo->find($id);
    }

    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'duplicate' => false];
        }

        try {
            $id = $this->repo->create($input);
            return ['ok' => true, 'id' => $id];
        } catch (DuplicateRecordException $e) {
            return ['ok' => false, 'errors' => ['email' => $e->getMessage()], 'duplicate' => true];
        }
    }

    /** Validate + update. Trả về ['ok'=>true] hoặc ['ok'=>false, 'errors'=>[], 'duplicate'=>bool]. */
    public function update(int $id, array $input): array
    {
        $errors = $this->validate($input);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'duplicate' => false];
        }

        try {
            $this->repo->update($id, $input);
            return ['ok' => true];
        } catch (DuplicateRecordException $e) {
            return ['ok' => false, 'errors' => ['email' => $e->getMessage()], 'duplicate' => true];
        }
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }

    public function validate(array $input): array
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

    private function len(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
