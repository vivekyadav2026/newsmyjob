<?php
/**
 * Visitor & Analytics Model
 */

declare(strict_types=1);

class VisitorModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTotalVisitors(): int
    {
        return (int) $this->db->query("SELECT COUNT(DISTINCT ip_address) FROM visitors")->fetchColumn();
    }

    public function getTodayVisitors(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE visit_date = ?");
        $stmt->execute([date('Y-m-d')]);
        return (int) $stmt->fetchColumn();
    }

    public function getMonthlyVisitors(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE visit_date >= ?"
        );
        $stmt->execute([date('Y-m-01')]);
        return (int) $stmt->fetchColumn();
    }

    public function getVisitorStats(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT visit_date, COUNT(DISTINCT ip_address) as visitors, COUNT(*) as page_hits
             FROM visitors WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY visit_date ORDER BY visit_date ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getPageViewStats(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT view_date, COUNT(*) as views FROM page_views
             WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY view_date ORDER BY view_date ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getRecentActivities(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT al.*, u.full_name, u.username FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
