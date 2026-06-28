<?php
/**
 * Advertisement Model
 */

declare(strict_types=1);

class AdvertisementModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM advertisements WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM advertisements ORDER BY display_order ASC, created_at DESC');
        return $stmt->fetchAll();
    }

    public function getByPosition(string $position): array
    {
        $stmt = $this->db->prepare('SELECT * FROM advertisements WHERE position = ? AND status = "active" AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY display_order ASC');
        $stmt->execute([$position]);
        return $stmt->fetchAll();
    }

    public function getByType(string $type): array
    {
        $stmt = $this->db->prepare('SELECT * FROM advertisements WHERE ad_type = ? AND status = "active" ORDER BY display_order ASC');
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO advertisements (title, ad_type, position, image, link, ad_code, width, height, start_date, end_date, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['title'], $data['ad_type'], $data['position'] ?? 'header',
            $data['image'] ?? null, $data['link'] ?? null, $data['ad_code'] ?? null,
            $data['width'] ?? null, $data['height'] ?? null,
            $data['start_date'] ?? null, $data['end_date'] ?? null,
            $data['status'] ?? 'active', $data['display_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE advertisements SET title=?, ad_type=?, position=?, image=?, link=?, ad_code=?, width=?, height=?, start_date=?, end_date=?, status=?, display_order=? WHERE id=?');
        return $stmt->execute([
            $data['title'], $data['ad_type'], $data['position'] ?? 'header',
            $data['image'] ?? null, $data['link'] ?? null, $data['ad_code'] ?? null,
            $data['width'] ?? null, $data['height'] ?? null,
            $data['start_date'] ?? null, $data['end_date'] ?? null,
            $data['status'] ?? 'active', $data['display_order'] ?? 0, $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM advertisements WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function incrementImpression(int $id): void
    {
        $this->db->prepare('UPDATE advertisements SET impressions = impressions + 1 WHERE id = ?')->execute([$id]);
    }

    public function incrementClick(int $id): void
    {
        $this->db->prepare('UPDATE advertisements SET clicks = clicks + 1 WHERE id = ?')->execute([$id]);
    }
}
