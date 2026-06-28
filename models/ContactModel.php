<?php
/**
 * Contact Messages Model
 */

declare(strict_types=1);

class ContactModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO contacts (name, email, subject, message, ip_address) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'], $data['email'],
            $data['subject'], $data['message'], getClientIp(),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getAll(int $page = 1, int $perPage = ADMIN_PER_PAGE): array
    {
        $offset = getOffset($page, $perPage);
        $total = (int) $this->db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
        $stmt = $this->db->prepare('SELECT * FROM contacts ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$perPage, $offset]);
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function markAsRead(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE contacts SET status = "read" WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contacts WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function countUnread(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM contacts WHERE status = "new"')->fetchColumn();
    }
}
