<?php
/**
 * Newsletter Model
 */

declare(strict_types=1);

class NewsletterModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function subscribe(string $email, ?string $name = null): bool
    {
        $existing = $this->findByEmail($email);
        if ($existing) {
            if ((int) $existing['status'] === 0) {
                $stmt = $this->db->prepare('UPDATE newsletters SET status = 1 WHERE email = ?');
                return $stmt->execute([$email]);
            }
            return true;
        }

        $stmt = $this->db->prepare('INSERT INTO newsletters (email, status) VALUES (?, 1)');
        return $stmt->execute([$email]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM newsletters WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(int $page = 1, int $perPage = ADMIN_PER_PAGE): array
    {
        $offset = getOffset($page, $perPage);
        $total = (int) $this->db->query('SELECT COUNT(*) FROM newsletters')->fetchColumn();
        $stmt = $this->db->prepare('SELECT * FROM newsletters ORDER BY subscribed_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$perPage, $offset]);
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function unsubscribe(string $token): bool
    {
        return false;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM newsletters WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countActive(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM newsletters WHERE status = 1')->fetchColumn();
    }
}
