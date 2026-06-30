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
            $stmt = $this->db->prepare('SELECT c.*, p.name as parent_name, (SELECT COUNT(*) FROM news n WHERE n.category_id = c.id AND n.status = "published") as news_count FROM categories c LEFT JOIN categories p ON c.parent_id = p.id WHERE c.status = ? ORDER BY c.display_order ASC, c.name ASC');
            $stmt->execute([$status]);
        } else {
            $stmt = $this->db->query('SELECT c.*, p.name as parent_name, (SELECT COUNT(*) FROM news n WHERE n.category_id = c.id) as news_count FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.display_order ASC, c.name ASC');
        }
        return $stmt->fetchAll();
    }

    public function getMenuCategories(): array
    {
        $stmt = $this->db->query('SELECT * FROM categories WHERE status = "active" AND show_in_menu = 1 ORDER BY display_order ASC, name ASC');
        $cats = $stmt->fetchAll();
        
        foreach ($cats as &$cat) {
            $stmtSub = $this->db->prepare('SELECT * FROM sub_categories WHERE category_id = ? AND status = "active" ORDER BY display_order ASC, name ASC');
            $stmtSub->execute([$cat['id']]);
            $cat['children'] = $stmtSub->fetchAll();
        }
        
        return $cats;
    }

    public function getHomeCategories(): array
    {
        $stmt = $this->db->query('SELECT * FROM categories WHERE status = "active" AND show_on_home = 1 ORDER BY display_order ASC, name ASC');
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO categories (parent_id, name, slug, description, image, icon, meta_title, meta_description, show_in_menu, show_on_home, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['parent_id'] ?? null, $data['name'], $data['slug'], $data['description'] ?? null,
            $data['image'] ?? null, $data['icon'] ?? null,
            $data['meta_title'] ?? null, $data['meta_description'] ?? null,
            $data['show_in_menu'] ?? 0, $data['show_on_home'] ?? 0, $data['display_order'] ?? 0,
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE categories SET parent_id=?, name=?, slug=?, description=?, image=?, icon=?, meta_title=?, meta_description=?, show_in_menu=?, show_on_home=?, display_order=?, status=? WHERE id=?');
        return $stmt->execute([
            $data['parent_id'] ?? null, $data['name'], $data['slug'], $data['description'] ?? null,
            $data['image'] ?? null, $data['icon'] ?? null,
            $data['meta_title'] ?? null, $data['meta_description'] ?? null,
            $data['show_in_menu'] ?? 0, $data['show_on_home'] ?? 0, $data['display_order'] ?? 0,
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
