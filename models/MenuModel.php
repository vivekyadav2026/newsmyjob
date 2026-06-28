<?php
/**
 * Menu Model
 */

declare(strict_types=1);

class MenuModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM menus WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(?string $location = null): array
    {
        if ($location) {
            $stmt = $this->db->prepare('SELECT * FROM menus WHERE menu_location = ? ORDER BY display_order ASC');
            $stmt->execute([$location]);
        } else {
            $stmt = $this->db->query('SELECT * FROM menus ORDER BY menu_location, display_order ASC');
        }
        return $stmt->fetchAll();
    }

    public function getActive(string $location): array
    {
        $stmt = $this->db->prepare('SELECT * FROM menus WHERE menu_location = ? AND status = "active" ORDER BY display_order ASC');
        $stmt->execute([$location]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO menus (title, url, parent_id, menu_location, target, icon, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['title'], $data['url'], $data['parent_id'] ?? null,
            $data['menu_location'] ?? 'header', $data['target'] ?? '_self',
            $data['icon'] ?? null, $data['display_order'] ?? 0,
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE menus SET title=?, url=?, parent_id=?, menu_location=?, target=?, icon=?, display_order=?, status=? WHERE id=?');
        return $stmt->execute([
            $data['title'], $data['url'], $data['parent_id'] ?? null,
            $data['menu_location'] ?? 'header', $data['target'] ?? '_self',
            $data['icon'] ?? null, $data['display_order'] ?? 0,
            $data['status'] ?? 'active', $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM menus WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
