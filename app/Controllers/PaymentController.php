<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\DuplicateRecordException;
use App\Repositories\PaymentRepository;
use App\Support\Response;

/**
 * Module B - Thanh toán học phí. Mã thanh toán (payment_code) là UNIQUE.
 */
class PaymentController
{
    private const STATUS_OPTIONS = ['pending', 'paid', 'refunded', 'cancelled'];
    private const PER_PAGE       = 10;

    public function index(): void
    {
        $q    = trim((string) ($_GET['q'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'created_at');
        $dir  = (string) ($_GET['dir'] ?? 'desc');
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $repo  = $this->repository();
        $total = $repo->countAll($q);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $lastPage);
        $offset = ($page - 1) * self::PER_PAGE;

        $payments = $repo->paginate($q, self::PER_PAGE, $offset, $sort, $dir);

        Response::view('payments/index', [
            'title'        => 'Quản lý Thanh toán học phí',
            'payments'     => $payments,
            'q'            => $q,
            'sort'         => $sort,
            'dir'          => $dir,
            'page'         => $page,
            'lastPage'     => $lastPage,
            'total'        => $total,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function create(): void
    {
        Response::view('payments/create', [
            'title'        => 'Thêm Thanh toán học phí',
            'errors'       => [],
            'old'          => $this->emptyInput(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function store(): void
    {
        csrf_verify();

        $old = $this->input();
        $errors = $this->validate($old);

        if ($errors !== []) {
            $this->renderForm('payments/create', 'Thêm Thanh toán học phí', $errors, $old, 422);
        }

        try {
            $this->repository()->create($old);
            flash_set('success', 'Đã thêm phiếu thanh toán học phí.');
            redirect('/payments');
        } catch (DuplicateRecordException $e) {
            $this->renderForm('payments/create', 'Thêm Thanh toán học phí', ['payment_code' => $e->getMessage()], $old, 409);
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $payment = $this->repository()->find($id);

        if ($payment === null) {
            Response::notFound('Không tìm thấy phiếu thanh toán cần sửa.');
        }

        Response::view('payments/edit', [
            'title'        => 'Sửa Thanh toán học phí',
            'errors'       => [],
            'old'          => $payment,
            'id'           => $id,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function update(): void
    {
        csrf_verify();

        $id = (int) ($_POST['id'] ?? 0);
        $repo = $this->repository();

        if ($repo->find($id) === null) {
            Response::notFound('Không tìm thấy phiếu thanh toán cần cập nhật.');
        }

        $old = $this->input();
        $old['id'] = $id;
        $errors = $this->validate($old);

        if ($errors !== []) {
            $this->renderForm('payments/edit', 'Sửa Thanh toán học phí', $errors, $old, 422);
        }

        try {
            $repo->update($id, $old);
            flash_set('success', 'Đã cập nhật phiếu thanh toán.');
            redirect('/payments');
        } catch (DuplicateRecordException $e) {
            $this->renderForm('payments/edit', 'Sửa Thanh toán học phí', ['payment_code' => $e->getMessage()], $old, 409);
        }
    }

    public function delete(): void
    {
        csrf_verify();

        $id = (int) ($_POST['id'] ?? 0);
        $this->repository()->delete($id);

        flash_set('success', 'Đã xóa phiếu thanh toán.');
        redirect('/payments');
    }

    private function renderForm(string $view, string $title, array $errors, array $old, int $status): void
    {
        Response::view($view, [
            'title'        => $title,
            'errors'       => $errors,
            'old'          => $old,
            'id'           => (int) ($old['id'] ?? 0),
            'statusLabels' => $this->statusLabels(),
        ], $status);
    }

    private function validate(array $input): array
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

        if (($input['course_name'] ?? '') === '') {
            $errors['course_name'] = 'Vui lòng nhập tên khóa học.';
        }

        if (!is_numeric($input['amount']) || (float) $input['amount'] < 0) {
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

    private function input(): array
    {
        return [
            'payment_code'  => trim((string) ($_POST['payment_code'] ?? '')),
            'student_name'  => trim((string) ($_POST['student_name'] ?? '')),
            'student_email' => trim((string) ($_POST['student_email'] ?? '')),
            'course_name'   => trim((string) ($_POST['course_name'] ?? '')),
            'amount'        => trim((string) ($_POST['amount'] ?? '0')),
            'status'        => trim((string) ($_POST['status'] ?? 'pending')),
            'note'          => trim((string) ($_POST['note'] ?? '')),
        ];
    }

    private function emptyInput(): array
    {
        return [
            'payment_code' => '', 'student_name' => '', 'student_email' => '',
            'course_name' => '', 'amount' => '', 'status' => 'pending', 'note' => '',
        ];
    }

    private function repository(): PaymentRepository
    {
        return new PaymentRepository(Database::connection());
    }

    private function statusLabels(): array
    {
        return [
            'pending'   => 'Chờ thanh toán',
            'paid'      => 'Đã thanh toán',
            'refunded'  => 'Đã hoàn tiền',
            'cancelled' => 'Đã hủy',
        ];
    }

    private function len(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
