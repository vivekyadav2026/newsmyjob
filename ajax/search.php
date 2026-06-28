<?php
/**
 * AJAX Live Search
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

$query = Security::sanitize($_GET['q'] ?? '');

if (strlen($query) < 2) {
    jsonResponse(['results' => []]);
}

$newsModel = new NewsModel();
$result = $newsModel->search($query, [], 1, 8);

$items = [];
foreach ($result['data'] as $article) {
    $items[] = [
        'title'    => $article['title'],
        'url'      => newsUrl($article['slug']),
        'category' => $article['category_name'] ?? '',
        'date'     => timeAgo($article['published_at']),
    ];
}

jsonResponse(['results' => $items]);
