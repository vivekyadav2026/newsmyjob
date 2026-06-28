<?php
/**
 * News API - JSON endpoints
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$newsModel = new NewsModel();
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = min(50, max(1, (int) ($_GET['limit'] ?? 12)));

$filters = [];
if (!empty($_GET['category'])) {
    $filters['category_slug'] = Security::sanitize($_GET['category']);
}
if (!empty($_GET['featured'])) {
    $filters['is_featured'] = 1;
}

$result = $newsModel->getPublished($filters, $page, $limit);

$articles = array_map(function ($item) {
    return [
        'id'       => (int) $item['id'],
        'title'    => $item['title'],
        'slug'     => $item['slug'],
        'excerpt'  => $item['excerpt'],
        'image'    => $item['featured_image'] ? UPLOADS_URL . '/' . $item['featured_image'] : null,
        'category' => $item['category_name'] ?? null,
        'author'   => $item['author_name'] ?? null,
        'views'    => (int) $item['views'],
        'url'      => newsUrl($item['slug']),
        'published'=> $item['published_at'],
    ];
}, $result['data']);

jsonResponse([
    'success' => true,
    'total'   => $result['total'],
    'page'    => $page,
    'data'    => $articles,
]);
