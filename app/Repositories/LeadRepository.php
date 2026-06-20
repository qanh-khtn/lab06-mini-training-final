<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DuplicateRecordException;
use PDO;
use PDOException;

class LeadRepository
{
    private const SORT_WHITELIST = ['id', 'full_name', 'email', 'phone', 'course_interest', 'care_status', 'created_at'];
    private const DIR_WHITELIST  = ['asc', 'desc'];

    public function __construct(private PDO $db) {}

    public function countAll(string $keyword = '', string $status = ''): int
    {
        [$where, $params] = $this->buildWhere($keyword, $status);
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM leads' . $where);
        $stmt->execute($params);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function paginate(string $keyword, int $limit, int $offset, string $sort, string $direction, string $status = ''): array
    {
        $sort      = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'id';
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'asc';

        [$where, $params] = $this->buildWhere($keyword, $status);
        $sql = "SELECT id, full_name, email, phone, course_interest, care_status, created_at
                FROM leads{$where}
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
        $stmt = $this->db->prepare('SELECT * FROM leads WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO leads (full_name, email, phone, course_interest, care_status, note)
                VALUES (:full_name, :email, :phone, :course_interest, :care_status, :note)';
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
        $sql = 'UPDATE leads
                SET full_name = :full_name, email = :email, phone = :phone,
                    course_interest = :course_interest, care_status = :care_status, note = :note
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
        $stmt = $this->db->prepare('UPDATE leads SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');

        return $stmt->execute(['id' => $id]);
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
            $conditions[] = '(full_name LIKE :kw1 OR email LIKE :kw2 OR phone LIKE :kw3)';
            $like = '%' . $keyword . '%';
            $params['kw1'] = $like;
            $params['kw2'] = $like;
            $params['kw3'] = $like;
        }

        if ($status !== '') {
            $conditions[] = 'care_status = :status';
            $params['status'] = $status;
        }

        return [' WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function bindData(array $data): array
    {
        return [
            'full_name'       => $data['full_name'],
            'email'           => $data['email'],
            'phone'           => ($data['phone'] ?? '') !== '' ? $data['phone'] : null,
            'course_interest' => $data['course_interest'],
            'care_status'     => $data['care_status'],
            'note'            => ($data['note'] ?? '') !== '' ? $data['note'] : null,
        ];
    }

    private function rethrowDuplicate(PDOException $e): never
    {
        if (($e->errorInfo[1] ?? null) === 1062) {
            throw new DuplicateRecordException('Email tư vấn này đã tồn tại trong hệ thống.');
        }

        throw $e;
    }
}
