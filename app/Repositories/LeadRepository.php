<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DuplicateRecordException;
use PDO;
use PDOException;

/**
 * Toàn bộ SQL của module Lead tư vấn nằm ở đây (Repository pattern).
 * Controller/View KHÔNG được viết SQL.
 */
class LeadRepository
{
    /** Cột được phép ORDER BY (whitelist chống SQL Injection qua URL). */
    private const SORT_WHITELIST = ['id', 'full_name', 'email', 'phone', 'course_interest', 'care_status', 'created_at'];
    private const DIR_WHITELIST  = ['asc', 'desc'];

    public function __construct(private PDO $db)
    {
    }

    public function countAll(string $keyword = ''): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM leads';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' WHERE full_name LIKE :kw OR email LIKE :kw OR phone LIKE :kw';
            $params['kw'] = '%' . $keyword . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Danh sách có search + pagination + sort an toàn.
     * Chỉ SELECT cột cần hiển thị (không dùng SELECT *).
     */
    public function paginate(string $keyword, int $limit, int $offset, string $sort, string $direction): array
    {
        $sort = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'created_at';
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'desc';

        $sql = 'SELECT id, full_name, email, phone, course_interest, care_status, created_at FROM leads';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' WHERE full_name LIKE :kw OR email LIKE :kw OR phone LIKE :kw';
            $params['kw'] = '%' . $keyword . '%';
        }

        // sort/direction đã whitelist nên nhúng an toàn; LIMIT/OFFSET bind kiểu INT
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
        $stmt = $this->db->prepare('SELECT * FROM leads WHERE id = :id');
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
        $stmt = $this->db->prepare('DELETE FROM leads WHERE id = :id');

        return $stmt->execute(['id' => $id]);
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
