<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Support\Response;

/**
 * Module B — Controller mỏng: chỉ đọc HTTP input và gọi redirect/view.
 * Toàn bộ validation + logic nghiệp vụ nằm trong PaymentService.
 */
class PaymentController
{
    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function index(): void
    {
        require_login();

        $q       = trim((string) ($_GET['q'] ?? ''));
        $status  = trim((string) ($_GET['status'] ?? ''));
        $sort    = (string) ($_GET['sort'] ?? 'id');
        $dir     = (string) ($_GET['dir'] ?? 'asc');
        $rawPage = (int) ($_GET['page'] ?? 1);

        $result = $this->service()->paginate($q, $status, $rawPage, $sort, $dir);

        if ($rawPage !== $result['page']) {
            $qs = query_string(['page' => $result['page']]);
            redirect('/payments' . ($qs ? '?' . $qs : ''));
        }

        Response::view('payments/index', [
            'title'         => 'Quản lý Thanh toán học phí',
            'payments'      => $result['rows'],
            'q'             => $q,
            'status'        => $status,
            'sort'          => $sort,
            'dir'           => $dir,
            'page'          => $result['page'],
            'lastPage'      => $result['lastPage'],
            'total'         => $result['total'],
            'statusLabels'  => $this->statusLabels(),
            'courseOptions' => PaymentService::COURSE_OPTIONS,
        ]);
    }

    public function create(): void
    {
        require_login();

        Response::view('payments/create', [
            'title'         => 'Thêm Thanh toán học phí',
            'errors'        => [],
            'old'           => $this->emptyInput(),
            'statusLabels'  => $this->statusLabels(),
            'courseOptions' => PaymentService::COURSE_OPTIONS,
        ]);
    }

    public function store(): void
    {
        require_login();
        csrf_verify();

        $input  = $this->input();
        $result = $this->service()->create($input);

        if (!$result['ok']) {
            $this->renderForm('payments/create', 'Thêm Thanh toán học phí', $result['errors'], $input, $result['duplicate'] ? 409 : 422);
        }

        flash_set('success', 'Đã thêm phiếu thanh toán học phí.');
        redirect('/payments');
    }

    public function edit(): void
    {
        require_login();

        $id      = (int) ($_GET['id'] ?? 0);
        $payment = $this->service()->find($id);

        if ($payment === null) {
            Response::notFound('Không tìm thấy phiếu thanh toán cần sửa.');
        }

        Response::view('payments/edit', [
            'title'         => 'Sửa Thanh toán học phí',
            'errors'        => [],
            'old'           => $payment,
            'id'            => $id,
            'statusLabels'  => $this->statusLabels(),
            'courseOptions' => PaymentService::COURSE_OPTIONS,
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
            Response::notFound('Không tìm thấy phiếu thanh toán cần cập nhật.');
        }

        $result = $this->service()->update($id, $input);

        if (!$result['ok']) {
            $this->renderForm('payments/edit', 'Sửa Thanh toán học phí', $result['errors'], $input, $result['duplicate'] ? 409 : 422);
        }

        flash_set('success', 'Đã cập nhật phiếu thanh toán.');
        redirect('/payments');
    }

    public function delete(): void
    {
        require_login();
        csrf_verify();

        $id = (int) ($_POST['id'] ?? 0);
        $this->service()->delete($id);

        flash_set('success', 'Đã xóa phiếu thanh toán.');
        redirect('/payments');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function service(): PaymentService
    {
        return new PaymentService(new PaymentRepository(Database::connection()));
    }

    private function renderForm(string $view, string $title, array $errors, array $old, int $status): void
    {
        Response::view($view, [
            'title'         => $title,
            'errors'        => $errors,
            'old'           => $old,
            'id'            => (int) ($old['id'] ?? 0),
            'statusLabels'  => $this->statusLabels(),
            'courseOptions' => PaymentService::COURSE_OPTIONS,
        ], $status);
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

    private function statusLabels(): array
    {
        return [
            'pending'   => 'Chờ thanh toán',
            'paid'      => 'Đã thanh toán',
            'refunded'  => 'Đã hoàn tiền',
            'cancelled' => 'Đã hủy',
        ];
    }
}
