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
    <link href="<?= asset('css/style.css') ?>?v=<?= time() ?>" rel="stylesheet">
    <style>:root { --primary-color: <?= e(setting('theme_color', '#dc3545')) ?>; }</style>
    <?= setting('google_analytics') ? setting('google_analytics') : '' ?>
    <?= setting('header_code') ? setting('header_code') : '' ?>
    <script>const BASE_URL = '<?= BASE_URL ?>'; const CSRF_TOKEN = '<?= csrfToken() ?>';</script>
</head>
<body>
<?php
$breakingModel = new BreakingNewsModel();
$breakingNews = $breakingModel->getActive();
$menuModel = new MenuModel();
$headerMenus = $menuModel->getActive('header');
$categoryModel = new CategoryModel();
$menuCategories = $categoryModel->getMenuCategories();
?>

<!-- Top Utility Bar (Dark, Premium) -->
<div class="top-header d-none d-lg-block bg-dark text-light border-bottom border-secondary" style="font-size: 12px; letter-spacing: 0.5px;">
    <div class="container py-2 d-flex justify-content-between align-items-center">
        <!-- Date (Left) -->
        <div class="d-flex align-items-center fw-semibold text-opacity-75" style="white-space: nowrap;">
            <i class="bi bi-calendar3 me-2 text-danger"></i> <?= date('l, F j, Y') ?>
        </div>
        
        <!-- Breaking News (Middle) -->
        <?php if ($breakingNews): ?>
        <div class="breaking-ticker mx-4 overflow-hidden flex-grow-1 d-flex align-items-center" style="height: 18px; position: relative;">
            <span class="premium-breaking-badge z-2 position-relative d-flex align-items-center">
                <span class="pulse-dot me-2"></span> BREAKING
            </span>
            <div class="ticker-content position-absolute d-flex align-items-center h-100" style="animation: tickerScroll 30s linear infinite; white-space: nowrap; left: 85px;">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <?php foreach ($breakingNews as $bn): ?>
                        <?php $link = $bn['link'] ?? ($bn['news_slug'] ? newsUrl($bn['news_slug']) : '#'); ?>
                        <a href="<?= e($link) ?>" class="text-light text-decoration-none fw-semibold me-5 hover-opacity">
                            <i class="bi bi-circle-fill text-danger me-2" style="font-size: 6px; vertical-align: middle;"></i><?= e($bn['title']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="flex-grow-1"></div>
        <?php endif; ?>

        <!-- Social Icons (Right) -->
        <div class="d-flex align-items-center" style="white-space: nowrap;">
            <div class="social-icons d-flex gap-3">
                <a href="https://x.com/Toolxy5" target="_blank" class="text-light text-opacity-75 text-decoration-none hover-white transition"><i class="bi bi-twitter-x"></i></a>
                <a href="https://www.instagram.com/toolxy.in?igsh=dHIwNnMyajdicnN3" target="_blank" class="text-light text-opacity-75 text-decoration-none hover-white transition"><i class="bi bi-instagram"></i></a>
                <a href="https://www.linkedin.com/in/md-anish-35122540b" target="_blank" class="text-light text-opacity-75 text-decoration-none hover-white transition"><i class="bi bi-linkedin"></i></a>
                <a href="https://www.threads.net/@toolxy.in" target="_blank" class="text-light text-opacity-75 text-decoration-none hover-white transition"><i class="bi bi-threads"></i></a>
                <a href="https://pin.it/2nsgB5SIm" target="_blank" class="text-light text-opacity-75 text-decoration-none hover-white transition"><i class="bi bi-pinterest"></i></a>
                <a href="https://www.quora.com/profile/Md-Anish-290" target="_blank" class="text-light text-opacity-75 text-decoration-none hover-white transition"><i class="bi bi-quora"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Main Header (Logo & Search) -->
<header class="site-header bg-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 gap-md-3 m-0 text-decoration-none" href="<?= BASE_URL ?>">
            <?php if (setting('site_logo')): ?>
                <img src="<?= UPLOADS_URL ?>/<?= e(setting('site_logo')) ?>" alt="<?= e(setting('site_name')) ?>" class="site-logo-img">
            <?php else: ?>
                <img src="<?= asset('logo/logo.png') ?>" alt="MyJobHub" class="site-logo-img">
            <?php endif; ?>
            <div class="d-flex flex-column justify-content-center mt-1">
                <span class="fw-black site-logo-title">MyJobHub</span>
                <span class="text-danger fw-bold text-uppercase mt-1 site-logo-subtitle">News & Updates</span>
            </div>
        </a>
        
        <!-- Search & Actions -->
        <div class="d-flex align-items-center gap-3 d-none d-lg-flex">
            <div class="search-wrapper position-relative">
                <input type="text" id="liveSearch" class="form-control rounded-pill px-4" placeholder="Search articles..." style="width:280px; background: #f8f9fa; border: 1px solid #e9ecef; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); height: 42px;">
                <i class="bi bi-search position-absolute text-muted" style="right: 16px; top: 50%; transform: translateY(-50%);"></i>
                <div id="searchResults" class="search-results-dropdown shadow-lg border-0 rounded-3 mt-2"></div>
            </div>
            <?php if (setting('enable_dark_mode') === '1'): ?>
            <button class="btn btn-light rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center hover-lift" id="darkModeBtn" style="width:42px;height:42px; background: #f8f9fa;"><i class="bi bi-moon-stars text-dark"></i></button>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler d-lg-none btn btn-light border shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-3"></i>
        </button>
    </div>
</header>

<!-- Navigation Menu (Centered, Elegant) -->
<nav class="navbar navbar-expand-lg bg-white border-top border-bottom sticky-top shadow-sm py-0 z-3">
    <div class="container">
        <div class="collapse navbar-collapse justify-content-center" id="mainNav">
            <!-- Mobile Search Bar (Visible only on mobile) -->
            <div class="d-lg-none mt-3 mb-2 px-3">
                <form action="<?= BASE_URL ?>/search" method="GET" class="position-relative">
                    <input type="text" name="q" class="form-control rounded-pill px-4 bg-light" placeholder="Search articles..." style="height: 42px;">
                    <button type="submit" class="btn position-absolute top-50 end-0 translate-middle-y text-muted border-0 bg-transparent pe-3"><i class="bi bi-search"></i></button>
                </form>
            </div>
            
            <ul class="navbar-nav gap-2 gap-lg-4 py-2 py-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-bold text-uppercase premium-nav-link" href="<?= BASE_URL ?>" style="font-size: 13px; letter-spacing: 1px;">Home</a>
                </li>
                <?php foreach ($menuCategories as $cat): ?>
                    <?php if (empty($cat['children'])): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold text-uppercase premium-nav-link" href="<?= categoryUrl($cat['slug']) ?>" style="font-size: 13px; letter-spacing: 1px;"><?= e($cat['name']) ?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link fw-bold text-uppercase premium-nav-link dropdown-toggle" href="#" id="catDrop<?= $cat['id'] ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 13px; letter-spacing: 1px;">
                                <?= e($cat['name']) ?>
                            </a>
                            <ul class="dropdown-menu shadow border-0 mt-2 rounded-3" aria-labelledby="catDrop<?= $cat['id'] ?>">
                                <li><a class="dropdown-item fw-semibold" href="<?= categoryUrl($cat['slug']) ?>">All <?= e($cat['name']) ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php foreach ($cat['children'] as $child): ?>
                                <li><a class="dropdown-item fw-semibold" href="<?= categoryUrl($child['slug']) ?>"><?= e($child['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php foreach ($headerMenus as $menu): ?>
                    <?php 
                    $menuTitle = strtolower(trim($menu['title']));
                    $menuUrl = trim($menu['url'], '/');
                    if ($menuTitle === 'home' || $menuUrl === '' || $menuUrl === 'index.php') {
                        continue;
                    }
                    ?>
                <li class="nav-item">
                    <a class="nav-link fw-bold text-uppercase premium-nav-link" href="<?= str_starts_with($menu['url'], 'http') ? e($menu['url']) : BASE_URL . e($menu['url']) ?>" target="<?= e($menu['target']) ?>" style="font-size: 13px; letter-spacing: 1px;"><?= e($menu['title']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
.premium-breaking-badge {
    background: linear-gradient(135deg, #dc3545 0%, #ff4757 100%);
    color: #fff;
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 9px;
    letter-spacing: 1px;
    font-weight: 800;
    box-shadow: 0 2px 8px rgba(220,53,69,0.4);
    margin-right: 15px;
}
.pulse-dot {
    width: 5px;
    height: 5px;
    background-color: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 4px rgba(255, 255, 255, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}
@keyframes tickerScroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-25%); }
}
.premium-nav-link { position: relative; padding: 16px 5px !important; color: #111827 !important; transition: color 0.3s; }
.premium-nav-link:hover { color: var(--primary-color) !important; }
.premium-nav-link::after { content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 3px; background-color: var(--primary-color); transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.premium-nav-link:hover::after { width: 100%; }
/* Logo Styling */
.site-logo-img { height: 55px; width: auto; object-fit: contain; }
.site-logo-title { font-family: 'Merriweather', serif; font-size: 2.1rem; color: #111827; letter-spacing: -1.2px; line-height: 0.9; display: block; }
.site-logo-subtitle { font-size: 9px; letter-spacing: 2px; display: block; }

/* Mobile Responsiveness Improvements */
@media (max-width: 991.98px) {
    .premium-nav-link { padding: 10px 15px !important; }
    .premium-nav-link::after { display: none; }
    .navbar-collapse { background: #fff; padding-bottom: 15px; border-radius: 0 0 15px 15px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); position: absolute; width: 100%; top: 100%; left: 0; z-index: 1000; }
    .dropdown-menu { border: none !important; box-shadow: none !important; padding-left: 15px; background: #f8f9fa; }
    .site-logo-title { font-size: 1.8rem; letter-spacing: -1px; }
    .site-logo-img { height: 58px; }
}
@media (max-width: 575.98px) {
    .site-logo-title { font-size: 1.6rem; letter-spacing: -0.8px; }
    .site-logo-subtitle { font-size: 8.5px; letter-spacing: 1.5px; margin-top: 2px !important; }
    .site-logo-img { height: 55 px; }
    .navbar-brand.gap-2 { gap: 10px !important; }
}
</style>

