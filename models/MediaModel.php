<?php
/**
 * Media Library Model
 */

declare(strict_types=1);

class MediaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT m.*, u.name as uploader_name FROM media m LEFT JOIN users u ON m.uploaded_by = u.id WHERE m.id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 24): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['file_type'])) {
            $where[] = 'm.file_type = ?';
            $params[] = $filters['file_type'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(m.original_name LIKE ? OR m.alt_text LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        $offset = getOffset($page, $perPage);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM media m WHERE $whereClause");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $queryParams = array_merge($params, [$perPage, $offset]);
        $stmt = $this->db->prepare("SELECT m.*, u.name as uploader_name FROM media m LEFT JOIN users u ON m.uploaded_by = u.id WHERE $whereClause ORDER BY m.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($queryParams);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO media (filename, original_name, file_path, file_type, mime_type, file_size, alt_text, caption, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['filename'], $data['original_name'], $data['file_path'],
            $data['file_type'], $data['mime_type'], $data['file_size'],
            $data['alt_text'] ?? null, $data['caption'] ?? null,
            $data['uploaded_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE media SET alt_text=?, caption=? WHERE id=?');
        return $stmt->execute([$data['alt_text'] ?? null, $data['caption'] ?? null, $id]);
    }

    public function delete(int $id): bool
    {
        $media = $this->findById($id);
        if ($media) {
            deleteUploadedFile($media['file_path']);
        }
        $stmt = $this->db->prepare('DELETE FROM media WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
