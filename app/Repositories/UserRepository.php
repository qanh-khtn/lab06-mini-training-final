<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\DuplicateRecordException;
use PDO;
use PDOException;

class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, password_hash, role, status
             FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $name, string $email, string $passwordHash): int
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (name, email, password_hash, role, status)
                 VALUES (:name, :email, :ph, :role, :status)'
            );
            $stmt->execute([
                'name'   => $name,
                'email'  => $email,
                'ph'     => $passwordHash,
                'role'   => 'staff',
                'status' => 'pending',
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), '1062')) {
                throw new DuplicateRecordException('Email đã được đăng ký.');
            }
            throw $e;
        }
    }

    /**
     * Find an active user by email, or create one on first login (used by
     * mock social login — simulates "auto-create account from provider email").
     */
    public function findOrCreateActive(string $name, string $email): array
    {
        $existing = $this->findByEmail($email);
        if ($existing !== null) {
            return $existing;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role, status)
             VALUES (:name, :email, :ph, :role, :status)'
        );
        $stmt->execute([
            'name'   => $name,
            'email'  => $email,
            'ph'     => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => 12]),
            'role'   => 'staff',
            'status' => 'active',
        ]);

        return $this->findByEmail($email);
    }

    public function findPending(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, role, status, created_at
             FROM users WHERE status = :status ORDER BY created_at DESC'
        );
        $stmt->execute(['status' => 'pending']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => 'active', 'id' => $id]);
    }

    public function reject(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id AND status = :status');
        return $stmt->execute(['id' => $id, 'status' => 'pending']);
    }
}
