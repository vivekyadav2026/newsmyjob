<?php
/**
 * Category Model
 */

declare(strict_types=1);

class CategoryModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = ? AND status = "active" LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(?string $status = null): array
    {
        if ($status) {
            $stmt = $this->db->prepare('SELECT c.*, (SELECT COUNT(*) FROM news n WHERE n.category_id = c.id AND n.status = "published") as news_count FROM categories c WHERE c.status = ? ORDER BY c.display_order ASC, c.name ASC');
            $stmt->execute([$status]);
        } else {
            $stmt = $this->db->query('SELECT c.*, (SELECT COUNT(*) FROM news n WHERE n.category_id = c.id) as news_count FROM categories c ORDER BY c.display_order ASC, c.name ASC');
        }
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, slug, description, image, icon, meta_title, meta_description, meta_keywords, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'], $data['slug'], $data['description'] ?? null,
            $data['image'] ?? null, $data['icon'] ?? null,
            $data['meta_title'] ?? null, $data['meta_description'] ?? null,
            $data['meta_keywords'] ?? null, $data['display_order'] ?? 0,
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE categories SET name=?, slug=?, description=?, image=?, icon=?, meta_title=?, meta_description=?, meta_keywords=?, display_order=?, status=? WHERE id=?');
        return $stmt->execute([
            $data['name'], $data['slug'], $data['description'] ?? null,
            $data['image'] ?? null, $data['icon'] ?? null,
            $data['meta_title'] ?? null, $data['meta_description'] ?? null,
            $data['meta_keywords'] ?? null, $data['display_order'] ?? 0,
            $data['status'] ?? 'active', $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = ?';
        $params = [$slug];
        if ($excludeId) { $sql .= ' AND id != ?'; $params[] = $excludeId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    }
}
