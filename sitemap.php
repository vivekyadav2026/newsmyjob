<?php
/**
 * Dynamic Sitemap Generator
 */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$newsModel = new NewsModel();
$categoryModel = new CategoryModel();
$categories = $categoryModel->getAll('active');
$news = $newsModel->getPublished([], 1, 10000);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$staticPages = ['', '/about', '/contact', '/privacy-policy', '/terms', '/videos', '/gallery', '/search'];
foreach ($staticPages as $page) {
    echo '<url><loc>' . e(BASE_URL . $page) . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . "\n";
}

foreach ($categories as $cat) {
    echo '<url><loc>' . e(categoryUrl($cat['slug'])) . '</loc><changefreq>daily</changefreq><priority>0.7</priority></url>' . "\n";
}

foreach ($news['data'] as $article) {
    $lastmod = date('Y-m-d', strtotime($article['updated_at']));
    echo '<url><loc>' . e(newsUrl($article['slug'])) . '</loc><lastmod>' . $lastmod . '</lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>' . "\n";
}

echo '</urlset>';
