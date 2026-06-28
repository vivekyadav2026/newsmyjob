<?php
/**
 * Categories API - JSON endpoints
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$model = new CategoryModel();
$categories = $model->getAll('active');

$data = array_map(function ($cat) {
    return [
        'id'          => (int) $cat['id'],
        'name'        => $cat['name'],
        'slug'        => $cat['slug'],
        'description' => $cat['description'],
        'image'       => $cat['image'] ? UPLOADS_URL . '/' . $cat['image'] : null,
        'news_count'  => (int) ($cat['news_count'] ?? 0),
        'url'         => categoryUrl($cat['slug']),
    ];
}, $categories);

jsonResponse(['success' => true, 'data' => $data]);
