<?php
/**
 * News Model - handles news articles CRUD
 */

declare(strict_types=1);

class NewsModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find news by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name, c.slug as category_slug, sc.name as sub_category_name, u.name as author_name, u.avatar as author_avatar FROM news n LEFT JOIN categories c ON n.category_id = c.id LEFT JOIN sub_categories sc ON n.sub_category_id = sc.id LEFT JOIN users u ON n.author_id = u.id WHERE n.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $news = $stmt->fetch();
        return $news ?: null;
    }

    /**
     * Find news by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name, c.slug as category_slug, sc.name as sub_category_name, u.name as author_name, u.avatar as author_avatar, u.bio as author_bio FROM news n LEFT JOIN categories c ON n.category_id = c.id LEFT JOIN sub_categories sc ON n.sub_category_id = sc.id LEFT JOIN users u ON n.author_id = u.id WHERE n.slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $news = $stmt->fetch();
        return $news ?: null;
    }

    /**
     * Get all news with filters
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = ADMIN_PER_PAGE): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'n.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'n.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['author_id'])) {
            $where[] = 'n.author_id = ?';
            $params[] = $filters['author_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(n.title LIKE ? OR n.excerpt LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['is_featured'])) {
            $where[] = 'n.is_featured = ?';
            $params[] = $filters['is_featured'];
        }
        if (isset($filters['is_trending'])) {
            $where[] = 'n.is_trending = ?';
            $params[] = $filters['is_trending'];
        }

        $whereClause = implode(' AND ', $where);
        $offset = getOffset($page, $perPage);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM news n WHERE $whereClause");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $queryParams = array_merge($params, [$perPage, $offset]);
        $stmt = $this->db->prepare("SELECT n.*, c.name as category_name, u.name as author_name FROM news n LEFT JOIN categories c ON n.category_id = c.id LEFT JOIN users u ON n.author_id = u.id WHERE $whereClause ORDER BY n.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($queryParams);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Get published news for frontend
     */
    public function getPublished(array $filters = [], int $page = 1, int $perPage = FRONTEND_PER_PAGE): array
    {
        $filters['status'] = 'published';
        $where = ['n.status = "published"', '(n.published_at IS NULL OR n.published_at <= NOW())'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'n.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['sub_category_id'])) {
            $where[] = 'n.sub_category_id = ?';
            $params[] = $filters['sub_category_id'];
        }
        if (!empty($filters['category_slug'])) {
            $where[] = 'c.slug = ?';
            $params[] = $filters['category_slug'];
        }
        if (!empty($filters['tag_slug'])) {
            $where[] = 't.slug = ?';
            $params[] = $filters['tag_slug'];
        }
        if (!empty($filters['author_id'])) {
            $where[] = 'n.author_id = ?';
            $params[] = $filters['author_id'];
        }

        $join = '';
        if (!empty($filters['tag_slug'])) {
            $join = ' INNER JOIN news_tags nt ON n.id = nt.news_id INNER JOIN tags t ON nt.tag_id = t.id';
        }

        $whereClause = implode(' AND ', $where);
        $offset = getOffset($page, $perPage);
        $orderBy = $filters['order_by'] ?? 'n.published_at DESC, n.created_at DESC';

        $countSql = "SELECT COUNT(DISTINCT n.id) FROM news n LEFT JOIN categories c ON n.category_id = c.id $join WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $queryParams = array_merge($params, [$perPage, $offset]);
        $sql = "SELECT DISTINCT n.*, c.name as category_name, c.slug as category_slug, u.name as author_name FROM news n LEFT JOIN categories c ON n.category_id = c.id LEFT JOIN users u ON n.author_id = u.id $join WHERE $whereClause ORDER BY $orderBy LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Create news article
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO news (title, slug, excerpt, content, featured_image, category_id, sub_category_id, author_id, status, is_featured, is_trending, is_editors_pick, featured_order, read_time, meta_title, meta_description, meta_keywords, canonical_url, og_image, video_url, youtube_embed, published_at, scheduled_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        $publishedAt = null;
        if ($data['status'] === 'published') {
            $publishedAt = date('Y-m-d H:i:s');
        } elseif ($data['status'] === 'scheduled' && !empty($data['scheduled_at'])) {
            $publishedAt = null;
        }

        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt'] ?? null,
            $data['content'],
            $data['featured_image'] ?? null,
            $data['category_id'] ?: null,
            $data['sub_category_id'] ?: null,
            $data['author_id'],
            $data['status'] ?? 'draft',
            $data['is_featured'] ?? 0,
            $data['is_trending'] ?? 0,
            $data['is_editors_pick'] ?? 0,
            $data['featured_order'] ?? 0,
            $data['read_time'] ?? calculateReadTime($data['content']),
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null,
            $data['meta_keywords'] ?? null,
            $data['canonical_url'] ?? null,
            $data['og_image'] ?? null,
            $data['video_url'] ?? null,
            $data['youtube_embed'] ?? null,
            $publishedAt,
            $data['scheduled_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update news article
     */
    public function update(int $id, array $data): bool
    {
        $current = $this->findById($id);
        if (!$current) {
            return false;
        }

        $publishedAt = $current['published_at'];
        if ($data['status'] === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $stmt = $this->db->prepare('UPDATE news SET title=?, slug=?, excerpt=?, content=?, featured_image=?, category_id=?, sub_category_id=?, status=?, is_featured=?, is_trending=?, is_editors_pick=?, featured_order=?, read_time=?, meta_title=?, meta_description=?, meta_keywords=?, canonical_url=?, og_image=?, video_url=?, youtube_embed=?, published_at=?, scheduled_at=? WHERE id=?');

        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt'] ?? null,
            $data['content'],
            $data['featured_image'] ?? null,
            $data['category_id'] ?: null,
            $data['sub_category_id'] ?: null,
            $data['status'] ?? 'draft',
            $data['is_featured'] ?? 0,
            $data['is_trending'] ?? 0,
            $data['is_editors_pick'] ?? 0,
            $data['featured_order'] ?? 0,
            $data['read_time'] ?? calculateReadTime($data['content']),
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null,
            $data['meta_keywords'] ?? null,
            $data['canonical_url'] ?? null,
            $data['og_image'] ?? null,
            $data['video_url'] ?? null,
            $data['youtube_embed'] ?? null,
            $publishedAt,
            $data['scheduled_at'] ?? null,
            $id,
        ]);
    }

    /**
     * Delete news article
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM news WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Increment view count
     */
    public function incrementViews(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE news SET views = views + 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Check slug uniqueness
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM news WHERE slug = ?';
        $params = [$slug];
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Get featured news
     */
    public function getFeatured(int $limit = 5): array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name, c.slug as category_slug, u.name as author_name FROM news n LEFT JOIN categories c ON n.category_id = c.id LEFT JOIN users u ON n.author_id = u.id WHERE n.status = "published" AND n.is_featured = 1 AND (n.published_at IS NULL OR n.published_at <= NOW()) ORDER BY n.featured_order ASC, n.published_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get trending news (manual + auto by views)
     */
    public function getTrending(int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name, c.slug as category_slug, u.name as author_name FROM news n LEFT JOIN categories c ON n.category_id = c.id LEFT JOIN users u ON n.author_id = u.id WHERE n.status = "published" AND (n.is_trending = 1 OR n.views > 0) AND (n.published_at IS NULL OR n.published_at <= NOW()) ORDER BY n.is_trending DESC, n.views DESC, n.published_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get most viewed news
     */
    public function getMostViewed(int $limit = 10): array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name, c.slug as category_slug, u.name as author_name FROM news n LEFT JOIN categories c ON n.category_id = c.id LEFT JOIN users u ON n.author_id = u.id WHERE n.status = "published" AND (n.published_at IS NULL OR n.published_at <= NOW()) ORDER BY n.views DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get editor's pick
     */
    public function getEditorsPick(int $limit = 6): array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name, c.slug as category_slug FROM news n LEFT JOIN categories c ON n.category_id = c.id WHERE n.status = "published" AND n.is_editors_pick = 1 AND (n.published_at IS NULL OR n.published_at <= NOW()) ORDER BY n.published_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get related news
     */
    public function getRelated(int $newsId, ?int $categoryId, int $limit = 4): array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name FROM news n LEFT JOIN categories c ON n.category_id = c.id WHERE n.id != ? AND n.status = "published" AND (n.category_id = ? OR ? IS NULL) AND (n.published_at IS NULL OR n.published_at <= NOW()) ORDER BY n.published_at DESC LIMIT ?');
        $stmt->execute([$newsId, $categoryId, $categoryId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get previous/next news
     */
    public function getAdjacent(int $id, string $direction = 'prev'): ?array
    {
        $operator = $direction === 'prev' ? '<' : '>';
        $order = $direction === 'prev' ? 'DESC' : 'ASC';
        $stmt = $this->db->prepare("SELECT id, title, slug FROM news WHERE id $operator ? AND status = 'published' ORDER BY id $order LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Search news
     */
    public function search(string $keyword, array $filters = [], int $page = 1, int $perPage = FRONTEND_PER_PAGE): array
    {
        $where = ['n.status = "published"', '(n.published_at IS NULL OR n.published_at <= NOW())'];
        $params = [];

        if ($keyword) {
            $where[] = '(n.title LIKE ? OR n.excerpt LIKE ? OR n.content LIKE ?)';
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'n.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(n.published_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(n.published_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $where);
        $offset = getOffset($page, $perPage);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM news n WHERE $whereClause");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $queryParams = array_merge($params, [$perPage, $offset]);
        $stmt = $this->db->prepare("SELECT n.*, c.name as category_name, c.slug as category_slug FROM news n LEFT JOIN categories c ON n.category_id = c.id WHERE $whereClause ORDER BY n.published_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($queryParams);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Sync tags for news
     */
    public function syncTags(int $newsId, array $tagNames): void
    {
        $this->db->prepare('DELETE FROM news_tags WHERE news_id = ?')->execute([$newsId]);

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;

            $slug = generateSlug($tagName);
            $stmt = $this->db->prepare('SELECT id FROM tags WHERE slug = ?');
            $stmt->execute([$slug]);
            $tag = $stmt->fetch();

            if (!$tag) {
                $this->db->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)')->execute([$tagName, $slug]);
                $tagId = (int) $this->db->lastInsertId();
            } else {
                $tagId = (int) $tag['id'];
            }

            $this->db->prepare('INSERT IGNORE INTO news_tags (news_id, tag_id) VALUES (?, ?)')->execute([$newsId, $tagId]);
        }
    }

    /**
     * Get tags for news
     */
    public function getTags(int $newsId): array
    {
        $stmt = $this->db->prepare('SELECT t.* FROM tags t INNER JOIN news_tags nt ON t.id = nt.tag_id WHERE nt.news_id = ?');
        $stmt->execute([$newsId]);
        return $stmt->fetchAll();
    }

    /**
     * Sync gallery images
     */
    public function syncImages(int $newsId, array $images): void
    {
        $this->db->prepare('DELETE FROM news_images WHERE news_id = ?')->execute([$newsId]);

        foreach ($images as $i => $image) {
            if (empty($image['path'])) continue;
            $this->db->prepare('INSERT INTO news_images (news_id, image_path, caption, display_order) VALUES (?, ?, ?, ?)')->execute([
                $newsId,
                $image['path'],
                $image['caption'] ?? null,
                $i,
            ]);
        }
    }

    /**
     * Get gallery images
     */
    public function getImages(int $newsId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM news_images WHERE news_id = ? ORDER BY display_order');
        $stmt->execute([$newsId]);
        return $stmt->fetchAll();
    }

    /**
     * Count by status
     */
    public function countByStatus(?string $status = null): int
    {
        if ($status) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM news WHERE status = ?');
            $stmt->execute([$status]);
        } else {
            $stmt = $this->db->query('SELECT COUNT(*) FROM news');
        }
        return (int) $stmt->fetchColumn();
    }

    /**
     * Publish scheduled news
     */
    public function publishScheduled(): int
    {
        $stmt = $this->db->prepare('UPDATE news SET status = "published", published_at = NOW() WHERE status = "scheduled" AND scheduled_at <= NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Get news by category for homepage
     */
    public function getByCategory(int $categoryId, int $limit = 4): array
    {
        $stmt = $this->db->prepare('SELECT n.*, u.name as author_name FROM news n LEFT JOIN users u ON n.author_id = u.id WHERE n.category_id = ? AND n.status = "published" AND (n.published_at IS NULL OR n.published_at <= NOW()) ORDER BY n.published_at DESC LIMIT ?');
        $stmt->execute([$categoryId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get video news
     */
    public function getVideos(int $limit = 6): array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name as category_name FROM news n LEFT JOIN categories c ON n.category_id = c.id WHERE n.status = "published" AND (n.video_url IS NOT NULL OR n.youtube_embed IS NOT NULL) AND (n.published_at IS NULL OR n.published_at <= NOW()) ORDER BY n.published_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
