<?php
/**
 * Frontend Router - Main Entry Point
 */
require_once __DIR__ . '/includes/bootstrap.php';

checkMaintenanceMode();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
if ($basePath && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath)) ?: '/';
}
$uri = rtrim($uri, '/') ?: '/';

trackPageView(null, 'page');

switch (true) {
    case $uri === '/':
        require __DIR__ . '/views/frontend/home.php';
        break;

    case preg_match('#^/news/([a-z0-9-]+)$#', $uri, $m):
        $_GET['slug'] = $m[1];
        require __DIR__ . '/views/frontend/news-detail.php';
        break;

    case preg_match('#^/category/([a-z0-9-]+)$#', $uri, $m):
        $_GET['slug'] = $m[1];
        require __DIR__ . '/views/frontend/category.php';
        break;

    case $uri === '/search':
        require __DIR__ . '/views/frontend/search.php';
        break;

    case $uri === '/about':
        require __DIR__ . '/views/frontend/about.php';
        break;

    case $uri === '/contact':
        require __DIR__ . '/views/frontend/contact.php';
        break;

    case $uri === '/privacy-policy':
        require __DIR__ . '/views/frontend/privacy.php';
        break;

    case $uri === '/terms':
        require __DIR__ . '/views/frontend/terms.php';
        break;

    case $uri === '/videos':
        require __DIR__ . '/views/frontend/videos.php';
        break;

    case $uri === '/gallery':
        require __DIR__ . '/views/frontend/gallery.php';
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/views/frontend/404.php';
        break;
}
