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

    public function countAll(string $keyword = '', string $status = '', string $dateFrom = '', string $dateTo = ''): int
    {
        [$where, $params] = $this->buildWhere($keyword, $status, $dateFrom, $dateTo);
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM payments' . $where);
        $stmt->execute($params);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function paginate(string $keyword, int $limit, int $offset, string $sort, string $direction, string $status = '', string $dateFrom = '', string $dateTo = ''): array
    {
        $sort      = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'id';
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'asc';

        [$where, $params] = $this->buildWhere($keyword, $status, $dateFrom, $dateTo);
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

    /** Trả toàn bộ danh sách khớp filter (không LIMIT) — dùng cho export CSV. */
    public function all(string $keyword, string $status, string $sort, string $direction, string $dateFrom = '', string $dateTo = ''): array
    {
        $sort      = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'id';
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'asc';

        [$where, $params] = $this->buildWhere($keyword, $status, $dateFrom, $dateTo);
        $sql = "SELECT id, payment_code, student_name, student_email, course_name, amount, status, created_at
                FROM payments{$where}
                ORDER BY {$sort} {$direction}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

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

    /** Soft delete hàng loạt trong 1 transaction. Trả về số dòng thực sự bị xóa. */
    public function deleteMany(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE payments SET deleted_at = NOW() WHERE id IN ({$placeholders}) AND deleted_at IS NULL"
            );
            $stmt->execute($ids);
            $affected = $stmt->rowCount();
            $this->db->commit();

            return $affected;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
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

    private function buildWhere(string $keyword, string $status, string $dateFrom = '', string $dateTo = ''): array
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

        if ($dateFrom !== '') {
            $conditions[] = 'created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== '') {
            $conditions[] = 'created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
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
