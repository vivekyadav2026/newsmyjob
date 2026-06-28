<?php
/**
 * Sub Category Model
 */

declare(strict_types=1);

class SubCategoryModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT sc.*, c.name as category_name FROM sub_categories sc LEFT JOIN categories c ON sc.category_id = c.id WHERE sc.id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(?int $categoryId = null, ?string $status = null): array
    {
        $where = ['1=1'];
        $params = [];
        if ($categoryId) { $where[] = 'sc.category_id = ?'; $params[] = $categoryId; }
        if ($status) { $where[] = 'sc.status = ?'; $params[] = $status; }
        $whereClause = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT sc.*, c.name as category_name FROM sub_categories sc LEFT JOIN categories c ON sc.category_id = c.id WHERE $whereClause ORDER BY sc.display_order ASC, sc.name ASC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByCategory(int $categoryId): array
    {
        return $this->getAll($categoryId, 'active');
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO sub_categories (category_id, name, slug, description, display_order, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['category_id'], $data['name'], $data['slug'],
            $data['description'] ?? null, $data['display_order'] ?? 0,
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE sub_categories SET category_id=?, name=?, slug=?, description=?, display_order=?, status=? WHERE id=?');
        return $stmt->execute([
            $data['category_id'], $data['name'], $data['slug'],
            $data['description'] ?? null, $data['display_order'] ?? 0,
            $data['status'] ?? 'active', $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM sub_categories WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM sub_categories WHERE slug = ?';
        $params = [$slug];
        if ($excludeId) { $sql .= ' AND id != ?'; $params[] = $excludeId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM sub_categories')->fetchColumn();
    }
}
