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

    public function countAll(string $keyword = '', string $status = '', string $dateFrom = '', string $dateTo = '', ?int $assignedToUserId = null): int
    {
        [$where, $params] = $this->buildWhere($keyword, $status, $dateFrom, $dateTo, $assignedToUserId);
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM leads l' . $where);
        $stmt->execute($params);

        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function paginate(string $keyword, int $limit, int $offset, string $sort, string $direction, string $status = '', string $dateFrom = '', string $dateTo = '', ?int $assignedToUserId = null): array
    {
        $sortColumn = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'id';
        $sort      = 'l.' . $sortColumn;
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'asc';

        [$where, $params] = $this->buildWhere($keyword, $status, $dateFrom, $dateTo, $assignedToUserId);
        $sql = "SELECT l.id, l.full_name, l.email, l.phone, l.course_interest, l.care_status, l.created_at, l.assigned_to, u.name AS assigned_to_name
                FROM leads l
                LEFT JOIN users u ON l.assigned_to = u.id
                {$where}
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
    public function all(string $keyword, string $status, string $sort, string $direction, string $dateFrom = '', string $dateTo = '', ?int $assignedToUserId = null): array
    {
        $sortColumn = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'id';
        $sort      = 'l.' . $sortColumn;
        $direction = in_array(strtolower($direction), self::DIR_WHITELIST, true) ? strtolower($direction) : 'asc';

        [$where, $params] = $this->buildWhere($keyword, $status, $dateFrom, $dateTo, $assignedToUserId);
        $sql = "SELECT l.id, l.full_name, l.email, l.phone, l.course_interest, l.care_status, l.created_at, l.assigned_to, u.name AS assigned_to_name
                FROM leads l
                LEFT JOIN users u ON l.assigned_to = u.id
                {$where}
                ORDER BY {$sort} {$direction}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

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
        $sql = 'INSERT INTO leads (full_name, email, phone, course_interest, care_status, note, assigned_to)
                VALUES (:full_name, :email, :phone, :course_interest, :care_status, :note, :assigned_to)';
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
                    course_interest = :course_interest, care_status = :care_status, note = :note,
                    assigned_to = :assigned_to
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
                "UPDATE leads SET deleted_at = NOW() WHERE id IN ({$placeholders}) AND deleted_at IS NULL"
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
     * Search leads by full_name, email, or phone (for quick search API).
     * $assignedToUserId lọc kết quả theo nhân viên phụ trách (staff chỉ tìm
     * thấy lead của mình) — bắt buộc truyền khi người gọi là role staff,
     * để tránh rò rỉ dữ liệu qua ô tìm kiếm nhanh trên topbar.
     */
    public function search(string $keyword, int $limit = 5, ?int $assignedToUserId = null): array
    {
        $like = '%' . $keyword . '%';
        $sql = '
            SELECT id, full_name, email, phone, course_interest, care_status, created_at
            FROM leads
            WHERE deleted_at IS NULL
              AND (full_name LIKE :kw1 OR email LIKE :kw2 OR phone LIKE :kw3)'
              . ($assignedToUserId !== null ? ' AND assigned_to = :assigned_to' : '') . '
            ORDER BY created_at DESC
            LIMIT :limit
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':kw1', $like);
        $stmt->bindValue(':kw2', $like);
        $stmt->bindValue(':kw3', $like);
        if ($assignedToUserId !== null) {
            $stmt->bindValue(':assigned_to', $assignedToUserId, \PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildWhere(string $keyword, string $status, string $dateFrom = '', string $dateTo = '', ?int $assignedToUserId = null): array
    {
        $conditions = ['l.deleted_at IS NULL'];
        $params     = [];

        if ($keyword !== '') {
            // Mỗi placeholder phải có tên riêng khi EMULATE_PREPARES = false
            $conditions[] = '(l.full_name LIKE :kw1 OR l.email LIKE :kw2 OR l.phone LIKE :kw3)';
            $like = '%' . $keyword . '%';
            $params['kw1'] = $like;
            $params['kw2'] = $like;
            $params['kw3'] = $like;
        }

        if ($status !== '') {
            $conditions[] = 'l.care_status = :status';
            $params['status'] = $status;
        }

        if ($dateFrom !== '') {
            $conditions[] = 'l.created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== '') {
            $conditions[] = 'l.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        if ($assignedToUserId !== null) {
            $conditions[] = 'l.assigned_to = :assigned_to';
            $params['assigned_to'] = $assignedToUserId;
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
            'assigned_to'     => isset($data['assigned_to']) && $data['assigned_to'] !== '' ? (int)$data['assigned_to'] : null,
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
