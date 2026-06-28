<?php
/**
 * Reports & Analytics Model
 */

declare(strict_types=1);

class ReportModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function trackVisit(string $ip, ?int $newsId = null, string $pageUrl = ''): void
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare('SELECT id FROM visitors WHERE ip_address = ? AND visit_date = ? LIMIT 1');
        $stmt->execute([$ip, $today]);
        if (!$stmt->fetch()) {
            $stmt = $this->db->prepare(
                'INSERT INTO visitors (ip_address, user_agent, referrer, page_url, visit_date) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $ip,
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $_SERVER['HTTP_REFERER'] ?? '',
                $pageUrl ?: ($_SERVER['REQUEST_URI'] ?? '/'),
                $today,
            ]);
        }

        $stmt = $this->db->prepare(
            'INSERT INTO page_views (news_id, page_url, ip_address, view_date) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$newsId, $pageUrl ?: ($_SERVER['REQUEST_URI'] ?? '/'), $ip, $today]);
    }

    public function getTotalVisitors(): int
    {
        return (int) $this->db->query('SELECT COUNT(DISTINCT ip_address) FROM visitors')->fetchColumn();
    }

    public function getTodayVisitors(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE visit_date = ?');
        $stmt->execute([date('Y-m-d')]);
        return (int) $stmt->fetchColumn();
    }

    public function getMonthlyVisitors(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(DISTINCT ip_address) FROM visitors WHERE visit_date >= ?');
        $stmt->execute([date('Y-m-01')]);
        return (int) $stmt->fetchColumn();
    }

    public function getTotalPageViews(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM page_views')->fetchColumn();
    }

    public function getVisitorChart(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT visit_date as date, COUNT(DISTINCT ip_address) as visitors
             FROM visitors WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY visit_date ORDER BY visit_date ASC'
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getPageViewChart(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT view_date as date, COUNT(*) as pageviews
             FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY view_date ORDER BY view_date ASC'
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getPopularCategories(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.name, c.slug, COUNT(n.id) as article_count
             FROM categories c LEFT JOIN news n ON c.id = n.category_id AND n.status = "published"
             GROUP BY c.id ORDER BY article_count DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getDashboardStats(): array
    {
        return [
            'total_visitors'   => $this->getTotalVisitors(),
            'today_visitors'   => $this->getTodayVisitors(),
            'monthly_visitors' => $this->getMonthlyVisitors(),
            'total_pageviews'  => $this->getTotalPageViews(),
        ];
    }
}
