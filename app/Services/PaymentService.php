<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DuplicateRecordException;
use App\Repositories\PaymentRepository;

/**
 * Tầng Service cho Module B (Thanh toán học phí).
 * Controller chỉ đọc HTTP input / gọi redirect — toàn bộ logic nghiệp vụ nằm ở đây.
 */
class PaymentService
{
    public const STATUS_OPTIONS = ['pending', 'paid', 'refunded', 'cancelled'];
    public const COURSE_OPTIONS = [
        'Lập trình Web'     => 'Lập trình Web (4.500.000đ)',
        'Lập trình Mobile'  => 'Lập trình Mobile (5.000.000đ)',
        'Phân tích dữ liệu' => 'Phân tích dữ liệu (5.500.000đ)',
        'AI ứng dụng'       => 'AI ứng dụng (6.500.000đ)',
        'AI nâng cao'       => 'AI nâng cao (8.000.000đ)',
        'Thiết kế đồ họa'   => 'Thiết kế đồ họa (4.000.000đ)',
        'Tiếng Anh CNTT'    => 'Tiếng Anh CNTT (3.500.000đ)',
        'Quản trị mạng'     => 'Quản trị mạng (4.800.000đ)',
    ];
    public const PER_PAGE = 10;

    public function __construct(private PaymentRepository $repo) {}

    public function paginate(string $q, string $status, int $rawPage, string $sort, string $dir, string $dateFrom = '', string $dateTo = ''): array
    {
        $dateFrom = $this->validDate($dateFrom);
        $dateTo   = $this->validDate($dateTo);

        $total    = $this->repo->countAll($q, $status, $dateFrom, $dateTo);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min(max(1, $rawPage), $lastPage);
        $offset   = ($page - 1) * self::PER_PAGE;
        $rows     = $this->repo->paginate($q, self::PER_PAGE, $offset, $sort, $dir, $status, $dateFrom, $dateTo);

        return compact('rows', 'total', 'page', 'lastPage');
    }

    /** Trả toàn bộ danh sách khớp filter (không phân trang) — dùng cho export CSV. */
    public function all(string $q, string $status, string $sort, string $dir, string $dateFrom = '', string $dateTo = ''): array
    {
        return $this->repo->all($q, $status, $sort, $dir, $this->validDate($dateFrom), $this->validDate($dateTo));
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
            return ['ok' => false, 'errors' => ['payment_code' => $e->getMessage()], 'duplicate' => true];
        }
    }

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
            return ['ok' => false, 'errors' => ['payment_code' => $e->getMessage()], 'duplicate' => true];
        }
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }

    public function validate(array $input): array
    {
        $errors = [];

        if (($input['payment_code'] ?? '') === '') {
            $errors['payment_code'] = 'Vui lòng nhập mã thanh toán.';
        } elseif ($this->len($input['payment_code']) > 50) {
            $errors['payment_code'] = 'Mã thanh toán tối đa 50 ký tự.';
        }

        if (($input['student_name'] ?? '') === '') {
            $errors['student_name'] = 'Vui lòng nhập tên học viên.';
        } elseif ($this->len($input['student_name']) > 100) {
            $errors['student_name'] = 'Tên học viên tối đa 100 ký tự.';
        }

        if (($input['student_email'] ?? '') !== '' && !filter_var($input['student_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['student_email'] = 'Email học viên không đúng định dạng.';
        }

        if (!in_array($input['course_name'] ?? '', array_keys(self::COURSE_OPTIONS), true)) {
            $errors['course_name'] = 'Vui lòng chọn khóa học hợp lệ.';
        }

        if (!is_numeric($input['amount'] ?? '') || (float) $input['amount'] < 0) {
            $errors['amount'] = 'Số tiền phải là số không âm.';
        }

        if (!in_array($input['status'] ?? '', self::STATUS_OPTIONS, true)) {
            $errors['status'] = 'Trạng thái thanh toán không hợp lệ.';
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
