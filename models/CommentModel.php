<?php
/**
 * Comment Model
 */

declare(strict_types=1);

class CommentModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByNewsId(int $newsId, bool $approvedOnly = true): array
    {
        $sql = "SELECT * FROM comments WHERE news_id = ?";
        if ($approvedOnly) {
            $sql .= " AND status = 'approved'";
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$newsId]);
        return $stmt->fetchAll();
    }

    public function getAll(int $page = 1, ?string $status = null): array
    {
        $offset = ($page - 1) * ADMIN_PER_PAGE;
        $where = '1=1';
        $params = [];

        if ($status) {
            $where .= ' AND c.status = ?';
            $params[] = $status;
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM comments c WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $params[] = ADMIN_PER_PAGE;
        $params[] = $offset;

        $stmt = $this->db->prepare(
            "SELECT c.*, n.title as news_title FROM comments c
             LEFT JOIN news n ON c.news_id = n.id WHERE {$where}
             ORDER BY c.created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO comments (news_id, user_id, name, email, comment, status, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['news_id'], $data['user_id'] ?? null, $data['name'], $data['email'],
            $data['comment'], $data['status'] ?? 'pending', getClientIp(),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->db->prepare("UPDATE comments SET status = ? WHERE id = ?")->execute([$status, $id]);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
    }
}
