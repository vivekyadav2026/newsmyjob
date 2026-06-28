<?php
/**
 * Activity Log Model
 */

declare(strict_types=1);

class ActivityLogModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log an activity
     */
    public static function log(?int $userId, string $action, string $moduleOrDescription = '', ?string $description = null, ?int $recordId = null): void
    {
        if ($description !== null) {
            $text = '[' . $moduleOrDescription . '] ' . $description;
            if ($recordId !== null) {
                $text .= ' (#' . $recordId . ')';
            }
        } else {
            $text = $moduleOrDescription;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $action, $text, getClientIp()]);
    }

    public function getRecent(int $limit = 20): array
    {
        $stmt = $this->db->prepare('SELECT a.*, u.name as user_name FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getAll(int $page = 1, int $perPage = ADMIN_PER_PAGE): array
    {
        $offset = getOffset($page, $perPage);
        $total = (int) $this->db->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn();
        $stmt = $this->db->prepare('SELECT a.*, u.name as user_name FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$perPage, $offset]);
        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }
}
