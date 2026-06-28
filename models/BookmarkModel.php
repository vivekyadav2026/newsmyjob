<?php
declare(strict_types=1);

class BookmarkModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function toggle(string $sessionId, int $newsId, ?int $userId = null): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM bookmarks WHERE session_id = ? AND news_id = ?');
        $stmt->execute([$sessionId, $newsId]);
        if ($stmt->fetch()) {
            $this->db->prepare('DELETE FROM bookmarks WHERE session_id = ? AND news_id = ?')->execute([$sessionId, $newsId]);
            return false;
        }

        $stmt = $this->db->prepare('INSERT INTO bookmarks (session_id, user_id, news_id) VALUES (?, ?, ?)');
        $stmt->execute([$sessionId, $userId, $newsId]);
        return true;
    }

    public function getBySession(string $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT n.* FROM bookmarks b INNER JOIN news n ON b.news_id = n.id
             WHERE b.session_id = ? AND n.status = "published" ORDER BY b.created_at DESC'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }
}
