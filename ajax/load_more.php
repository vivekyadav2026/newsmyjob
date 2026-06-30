<?php
/**
 * AJAX Load More News
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

$page = (int) ($_GET['page'] ?? 1);
$perPage = 5; // We want to load 5 at a time on home page

$newsModel = new NewsModel();
$result = $newsModel->getPublished([], $page, $perPage);

$data = $result['data'] ?? [];
$total = $result['total'] ?? 0;

$hasMore = ($page * $perPage) < $total;

$items = [];
foreach ($data as $item) {
    $items[] = [
        'id' => $item['id'],
        'slug' => $item['slug'],
        'title' => e($item['title']),
        'excerpt' => e(truncate($item['excerpt'] ?? strip_tags($item['content'] ?? ''), 120)),
        'featured_image' => $item['featured_image'] ? UPLOADS_URL . '/' . e($item['featured_image']) : asset('images/placeholder.svg'),
        'category_name' => e($item['category_name'] ?? ''),
        'published_at' => timeAgo($item['published_at'] ?? $item['created_at']),
        'views' => number_format((float)($item['views'] ?? 0)),
        'url' => newsUrl($item['slug'])
    ];
}

jsonResponse([
    'success' => true,
    'data' => $items,
    'hasMore' => $hasMore
]);
