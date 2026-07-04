<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DuplicateRecordException;
use PDO;
use PDOException;

class PaymentRepository
{
    private const SORT_WHITELIST = ['id', 'payment_code', 'student_name', 'student_email', 'course_name', 'amount', 'status', 'created_at'];
    private const DIR_WHITELIST  = ['asc', 'desc'];

    public function __construct(private PDO $db) {}

    public function countAll(string $keyword = '', string $status = ''): int
    {
        [$where, $params] = $this->buildWhere($keyword, $status);
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM payments' . $where);
        $stmt->execute($params);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function paginate(string $keyword, int $limit, int $offset, string $sort, string $direction, string $status = ''): array
    {
        $sort      = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'id';
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'asc';

        [$where, $params] = $this->buildWhere($keyword, $status);
        $sql = "SELECT id, payment_code, student_name, student_email, course_name, amount, status, created_at
                FROM payments{$where}
                ORDER BY {$sort} {$direction}
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM payments WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO payments (payment_code, student_name, student_email, course_name, amount, status, note)
                VALUES (:payment_code, :student_name, :student_email, :course_name, :amount, :status, :note)';
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($this->bindData($data));

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->rethrowDuplicate($e);
        }
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE payments
                SET payment_code = :payment_code, student_name = :student_name, student_email = :student_email,
                    course_name = :course_name, amount = :amount, status = :status, note = :note
                WHERE id = :id AND deleted_at IS NULL';
        try {
            $stmt = $this->db->prepare($sql);
            $params = $this->bindData($data);
            $params['id'] = $id;

            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->rethrowDuplicate($e);
        }
    }

    /** Soft delete: đánh dấu deleted_at thay vì xóa hàng khỏi DB. */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE payments SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');

        return $stmt->execute(['id' => $id]);
    }

    /**
     * Search payments by payment_code or student_name (for quick search API)
     */
    public function search(string $keyword, int $limit = 3): array
    {
        $like = '%' . $keyword . '%';
        $sql = '
            SELECT id, payment_code, student_name, course_name, amount, status, created_at
            FROM payments
            WHERE deleted_at IS NULL
              AND (payment_code LIKE :kw1 OR student_name LIKE :kw2)
            ORDER BY created_at DESC
            LIMIT :limit
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':kw1', $like);
        $stmt->bindValue(':kw2', $like);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildWhere(string $keyword, string $status): array
    {
        $conditions = ['deleted_at IS NULL'];
        $params     = [];

        if ($keyword !== '') {
            // Mỗi placeholder phải có tên riêng khi EMULATE_PREPARES = false
            $conditions[] = '(payment_code LIKE :kw1 OR student_name LIKE :kw2 OR student_email LIKE :kw3)';
            $like = '%' . $keyword . '%';
            $params['kw1'] = $like;
            $params['kw2'] = $like;
            $params['kw3'] = $like;
        }

        if ($status !== '') {
            $conditions[] = 'status = :status';
            $params['status'] = $status;
        }

        return [' WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function bindData(array $data): array
    {
        return [
            'payment_code'  => $data['payment_code'],
            'student_name'  => $data['student_name'],
            'student_email' => ($data['student_email'] ?? '') !== '' ? $data['student_email'] : null,
            'course_name'   => $data['course_name'],
            'amount'        => $data['amount'],
            'status'        => $data['status'],
            'note'          => ($data['note'] ?? '') !== '' ? $data['note'] : null,
        ];
    }

    private function rethrowDuplicate(PDOException $e): never
    {
        if (($e->errorInfo[1] ?? null) === 1062) {
            throw new DuplicateRecordException('Mã thanh toán này đã tồn tại. Vui lòng nhập mã khác.');
        }

        throw $e;
    }
}
