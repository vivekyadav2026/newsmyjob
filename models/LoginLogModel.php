<?php
/**
 * Login Log Model
 */

declare(strict_types=1);

class LoginLogModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO login_logs (user_id, email, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['user_id'], $data['email'], $data['ip_address'],
            $data['user_agent'], $data['success'] ? 'success' : 'failed',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getRecent(int $limit = 50): array
    {
        $stmt = $this->db->prepare('SELECT l.*, u.name as user_name FROM login_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
