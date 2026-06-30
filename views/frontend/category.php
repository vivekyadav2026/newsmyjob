<?php
/**
 * Category Page - Premium Redesign
 */
$slug = $_GET['slug'] ?? '';
$categoryModel = new CategoryModel();
$category = $categoryModel->findBySlug($slug);

if (!$category) {
    http_response_code(404);
    require VIEWS_PATH . '/frontend/404.php';
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$newsModel = new NewsModel();
$result = $newsModel->getPublished(['category_slug' => $slug], $page);
$subCategories = (new SubCategoryModel())->getByCategory((int) $category['id']);

$pageTitle = $category['meta_title'] ?: $category['name'] . ' - ' . setting('site_name');
$metaDescription = $category['meta_description'] ?: $category['description'] ?? '';

// Fetch "You May Also Like" if content is sparse
$moreNews = [];
if (count($result['data']) < 4) {
    $moreNews = $newsModel->getPublished([], 1, 3)['data'];
}

// Determine category icon
$catLower = strtolower($category['name']);
$catIcon = 'bi-folder';
if (str_contains($catLower, 'sport')) $catIcon = 'bi-trophy';
elseif (str_contains($catLower, 'politic')) $catIcon = 'bi-bank';
elseif (str_contains($catLower, 'tech')) $catIcon = 'bi-cpu';
elseif (str_contains($catLower, 'health')) $catIcon = 'bi-heart-pulse';
elseif (str_contains($catLower, 'business') || str_contains($catLower, 'economy')) $catIcon = 'bi-graph-up-arrow';
elseif (str_contains($catLower, 'entertainment') || str_contains($catLower, 'movie')) $catIcon = 'bi-film';

trackPageView(null, 'category');

require VIEWS_PATH . '/frontend/includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb custom-breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="bi bi-house-door-fill"></i> Home</a></li>
            <li class="breadcrumb-item active"><?= e($category['name']) ?></li>
        </ol>
    </nav>
<!-- 
    <div class="page-header mb-5 category-header-box d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="header-content position-relative z-1">
            <h1 class="page-title"><?= e($category['name']) ?></h1>
            <?php if ($category['description']): ?>
                <p class="category-description mb-0"><?= e($category['description']) ?></p>
            <?php endif; ?>
            
            <?php if ($subCategories): ?>
            <div class="topic-filters mt-4 d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small fw-bold text-uppercase me-2">Topics:</span>
                <?php foreach ($subCategories as $sub): ?>
                <a href="#" class="category-badge text-decoration-none"><?= e($sub['name']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="header-icon-widget d-none d-md-flex align-items-center justify-content-center text-primary opacity-25" style="font-size: 5rem; min-width: 120px;">
            <i class="bi <?= $catIcon ?>"></i>
        </div>
    </div> -->

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4">
                <?php if ($result['data']): ?>
                    <?php foreach ($result['data'] as $article): ?>
                        <?php $article['category_name'] = $category['name']; include VIEWS_PATH . '/frontend/includes/news-card-hz.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-muted">No news found in this category.</h4>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-5">
                <?= renderPagination($result['total'], $page, FRONTEND_PER_PAGE, categoryUrl($slug)) ?>
            </div>

            <?php if (!empty($moreNews)): ?>
                <div class="mt-5 pt-4 border-top">
                    <div class="section-title mb-4">
                        <h4>You May Also Like</h4>
                    </div>
                    <div class="row g-4">
                        <?php foreach ($moreNews as $article): ?>
                        <div class="col-md-4">
                            <div class="news-card-grid h-100 shadow-sm border-0 bg-white" style="border-radius: 12px; overflow: hidden; transition: transform 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                                <a href="<?= newsUrl($article['slug']) ?>" class="d-block" style="height: 160px; overflow: hidden;">
                                    <img src="<?= $article['featured_image'] ? UPLOADS_URL . '/' . e($article['featured_image']) : asset('images/placeholder.svg') ?>" alt="<?= e($article['title']) ?>" class="w-100 h-100 object-fit-cover" style="transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='none'">
                                </a>
                                <div class="p-3">
                                    <?php if (!empty($article['category_name'])): ?>
                                    <span class="badge bg-primary mb-2 px-2 py-1" style="font-size: 10px;"><?= e($article['category_name']) ?></span>
                                    <?php endif; ?>
                                    <h6 class="fw-bold mb-0" style="line-height: 1.4;"><a href="<?= newsUrl($article['slug']) ?>" class="text-dark text-decoration-none"><?= e($article['title']) ?></a></h6>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4 mt-5 mt-lg-0">
            <div class="sticky-top" style="top: 100px; z-index: 10;">
                <?php require VIEWS_PATH . '/frontend/includes/sidebar.php'; ?>
            </div>
        </div>
    </div>
</div>


<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
