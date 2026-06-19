<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DuplicateRecordException;
use PDO;
use PDOException;

/**
 * Toàn bộ SQL của module Thanh toán học phí nằm ở đây (Repository pattern).
 */
class PaymentRepository
{
    private const SORT_WHITELIST = ['id', 'payment_code', 'student_name', 'student_email', 'course_name', 'amount', 'status', 'created_at'];
    private const DIR_WHITELIST  = ['asc', 'desc'];

    public function __construct(private PDO $db)
    {
    }

    public function countAll(string $keyword = ''): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM payments';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' WHERE payment_code LIKE :kw OR student_name LIKE :kw OR student_email LIKE :kw';
            $params['kw'] = '%' . $keyword . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function paginate(string $keyword, int $limit, int $offset, string $sort, string $direction): array
    {
        $sort = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'created_at';
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'desc';

        $sql = 'SELECT id, payment_code, student_name, student_email, course_name, amount, status, created_at FROM payments';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' WHERE payment_code LIKE :kw OR student_name LIKE :kw OR student_email LIKE :kw';
            $params['kw'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset";

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
        $stmt = $this->db->prepare('SELECT * FROM payments WHERE id = :id');
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
                WHERE id = :id';

        try {
            $stmt = $this->db->prepare($sql);
            $params = $this->bindData($data);
            $params['id'] = $id;

            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->rethrowDuplicate($e);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM payments WHERE id = :id');

        return $stmt->execute(['id' => $id]);
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
