<?php
/**
 * Frontend Header Include
 */
if (!defined('APP_ROOT')) {
    require_once dirname(__DIR__) . '/includes/bootstrap.php';
}

$pageTitle = $pageTitle ?? setting('site_name', 'NewsMyJob');
$metaDescription = $metaDescription ?? setting('meta_description', '');
$metaKeywords = $metaKeywords ?? setting('meta_keywords', '');
$canonicalUrl = $canonicalUrl ?? BASE_URL . $_SERVER['REQUEST_URI'];
$ogImage = $ogImage ?? (setting('site_logo') ? UPLOADS_URL . '/' . setting('site_logo') : asset('img/default-og.jpg'));
$ogType = $ogType ?? 'website';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <?php if (setting('site_favicon')): ?>
    <link rel="icon" href="<?= UPLOADS_URL ?>/<?= e(setting('site_favicon')) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <style>:root { --primary-color: <?= e(setting('theme_color', '#dc3545')) ?>; }</style>
    <?= setting('google_analytics') ? setting('google_analytics') : '' ?>
    <?= setting('header_code') ? setting('header_code') : '' ?>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
<?php
$breakingModel = new BreakingNewsModel();
$breakingNews = $breakingModel->getActive();
$menuModel = new MenuModel();
$headerMenus = $menuModel->getActive('header');
?>

<!-- Top Header -->
<div class="top-header d-none d-lg-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span><i class="bi bi-calendar3"></i> <?= date('l, d M Y') ?></span>
            <span id="weatherWidget"><i class="bi bi-cloud-sun"></i> Loading...</span>
        </div>
        
        <?php if ($breakingNews): ?>
        <div class="breaking-ticker-wrap mx-3" style="flex: 1; border-radius: 4px;">
            <div class="breaking-label">BREAKING</div>
            <div class="breaking-ticker">
                <span class="ticker-content">
                    <?php foreach ($breakingNews as $bn): ?>
                        <?php $link = $bn['link'] ?? ($bn['news_slug'] ? newsUrl($bn['news_slug']) : '#'); ?>
                        <a href="<?= e($link) ?>"><i class="bi bi-circle-fill" style="font-size: 6px; vertical-align: middle; margin-right: 8px;"></i><?= e($bn['title']) ?></a>
                    <?php endforeach; ?>
                </span>
            </div>
        </div>
        <?php else: ?>
        <div class="flex-grow-1"></div>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3">
            <div class="social-icons">
                <a href="#" class="me-2"><i class="bi bi-facebook"></i></a>
                <a href="#" class="me-2"><i class="bi bi-twitter-x"></i></a>
                <a href="#" class="me-2"><i class="bi bi-youtube"></i></a>
            </div>
            <div class="dropdown">
                <a href="#" class="dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">EN</a>
                <ul class="dropdown-menu dropdown-menu-end text-small" style="min-width: auto;">
                    <li><a class="dropdown-item" href="#">English</a></li>
                    <li><a class="dropdown-item" href="#">Hindi</a></li>
                </ul>
            </div>
            <a href="#" class="btn btn-sm btn-outline-light py-0" style="font-size: 12px;"><i class="bi bi-person"></i> Login</a>
        </div>
    </div>
</div>

<!-- Main Header & Navigation -->
<header class="site-header">
    <div class="container py-3 d-flex justify-content-between align-items-center">
        <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="<?= BASE_URL ?>">
            <?php if (setting('site_logo')): ?>
                <img src="<?= UPLOADS_URL ?>/<?= e(setting('site_logo')) ?>" alt="<?= e(setting('site_name')) ?>" height="45">
            <?php else: ?>
                <i class="bi bi-newspaper"></i> <?= e(setting('site_name', 'NewsMyJob')) ?>
            <?php endif; ?>
        </a>
        
        <div class="d-flex align-items-center gap-3 d-none d-lg-flex">
            <div class="search-wrapper position-relative">
                <input type="text" id="liveSearch" class="form-control form-control-sm rounded-pill px-3" placeholder="Search news..." style="width:220px; background: #f1f1f1; border: none;">
                <i class="bi bi-search position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                <div id="searchResults" class="search-results-dropdown"></div>
            </div>
            <button class="btn btn-light rounded-circle" style="width:35px;height:35px;"><i class="bi bi-bell"></i></button>
            <?php if (setting('enable_dark_mode') === '1'): ?>
            <button class="btn btn-light rounded-circle" id="darkModeBtn" style="width:35px;height:35px;"><i class="bi bi-moon"></i></button>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler d-lg-none btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-3"></i>
        </button>
    </div>

    <!-- Navigation Below Header -->
    <nav class="navbar navbar-expand-lg py-0 border-top">
        <div class="container">
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>"><i class="bi bi-house-door-fill me-1"></i>Home</a>
                    </li>
                    <?php foreach ($headerMenus as $menu): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= str_starts_with($menu['url'], 'http') ? e($menu['url']) : BASE_URL . e($menu['url']) ?>" target="<?= e($menu['target']) ?>"><?= e($menu['title']) ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

