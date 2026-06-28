<?php
/**
 * Breaking News Model
 */

declare(strict_types=1);

class BreakingNewsModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT b.*, n.title as news_title, n.slug as news_slug FROM breaking_news b LEFT JOIN news n ON b.news_id = n.id WHERE b.id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT b.*, n.title as news_title, n.slug as news_slug FROM breaking_news b LEFT JOIN news n ON b.news_id = n.id ORDER BY b.display_order ASC, b.created_at DESC');
        return $stmt->fetchAll();
    }

    public function getActive(): array
    {
        $stmt = $this->db->query('SELECT b.*, n.slug as news_slug FROM breaking_news b LEFT JOIN news n ON b.news_id = n.id WHERE b.status = "active" ORDER BY b.display_order ASC');
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO breaking_news (title, news_id, link, status, display_order) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['title'], $data['news_id'] ?: null, $data['link'] ?? null,
            $data['status'] ?? 'active', $data['display_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE breaking_news SET title=?, news_id=?, link=?, status=?, display_order=? WHERE id=?');
        return $stmt->execute([
            $data['title'], $data['news_id'] ?: null, $data['link'] ?? null,
            $data['status'] ?? 'active', $data['display_order'] ?? 0, $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM breaking_news WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
