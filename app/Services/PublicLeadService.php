<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DuplicateRecordException;
use App\Repositories\LeadRepository;

/**
 * Service cho form công khai tạo lead (không cần đăng nhập).
 * Kiểm tra: honeypot → rate limit → validate → duplicate handling.
 */
class PublicLeadService
{
    public const COURSE_OPTIONS = ['web', 'mobile', 'data', 'ai', 'other'];
    public const RATE_LIMIT_SECONDS = 5;

    public function __construct(private LeadRepository $repo) {}

    /**
     * Tạo lead từ form công khai.
     * @return ['ok'=>bool, 'errors'=>[], 'duplicate'=>bool, 'honeypot'=>bool, 'rate_limit'=>bool]
     */
    public function create(array $input): array
    {
        // Kiểm tra honeypot: nếu field website có giá trị → bot
        if (!empty(trim((string)($input['website'] ?? '')))) {
            return [
                'ok' => false,
                'errors' => ['honeypot' => 'Yêu cầu không hợp lệ.'],
                'duplicate' => false,
                'honeypot' => true,
                'rate_limit' => false,
            ];
        }

        // Kiểm tra rate limit: tối thiểu 5 giây giữa 2 lần submit
        if (!rate_limit_check('public_lead', self::RATE_LIMIT_SECONDS)) {
            return [
                'ok' => false,
                'errors' => ['rate_limit' => 'Vui lòng đợi ' . self::RATE_LIMIT_SECONDS . ' giây trước khi gửi lại.'],
                'duplicate' => false,
                'honeypot' => false,
                'rate_limit' => true,
            ];
        }

        // Validate dữ liệu
        $errors = $this->validate($input);
        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'duplicate' => false,
                'honeypot' => false,
                'rate_limit' => false,
            ];
        }

        // Chuẩn bị dữ liệu: care_status luôn là 'new' cho form công khai
        $data = [
            'full_name' => trim((string)($input['full_name'] ?? '')),
            'email' => trim((string)($input['email'] ?? '')),
            'phone' => trim((string)($input['phone'] ?? '')),
            'course_interest' => trim((string)($input['course_interest'] ?? 'web')),
            'care_status' => 'new',
            'note' => trim((string)($input['note'] ?? '')),
        ];

        // Thử insert: bắt duplicate key exception
        try {
            $id = $this->repo->create($data);
            return [
                'ok' => true,
                'id' => $id,
                'errors' => [],
                'duplicate' => false,
                'honeypot' => false,
                'rate_limit' => false,
            ];
        } catch (DuplicateRecordException $e) {
            return [
                'ok' => false,
                'errors' => ['email' => $e->getMessage()],
                'duplicate' => true,
                'honeypot' => false,
                'rate_limit' => false,
            ];
        }
    }

    /**
     * Validate dữ liệu form công khai.
     */
    private function validate(array $input): array
    {
        $errors = [];

        $name = trim((string)($input['full_name'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $phone = trim((string)($input['phone'] ?? ''));
        $course = trim((string)($input['course_interest'] ?? ''));

        if ($name === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên.';
        } elseif (mb_strlen($name, 'UTF-8') > 100) {
            $errors['full_name'] = 'Họ và tên tối đa 100 ký tự.';
        }

        if ($email === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if ($phone !== '' && !preg_match('/^0[0-9]{9}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.';
        }

        if (!in_array($course, self::COURSE_OPTIONS, true)) {
            $errors['course_interest'] = 'Vui lòng chọn khóa học hợp lệ.';
        }

        return $errors;
    }
}
