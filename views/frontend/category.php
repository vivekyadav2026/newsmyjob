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

trackPageView(null, 'category');

require VIEWS_PATH . '/frontend/includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb fw-semibold" style="font-size: 13px;">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-dark"><i class="bi bi-house-door-fill"></i> Home</a></li>
            <li class="breadcrumb-item active text-danger"><?= e($category['name']) ?></li>
        </ol>
    </nav>

    <div class="page-header mb-4 pb-3 border-bottom border-2" style="border-color: var(--primary-color) !important;">
        <h1 class="display-5 fw-bold font-merriweather text-dark mb-2"><?= e($category['name']) ?></h1>
        <?php if ($category['description']): ?>
            <p class="fs-6 text-muted mb-0"><?= e($category['description']) ?></p>
        <?php endif; ?>
        
        <?php if ($subCategories): ?>
        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted small fw-bold text-uppercase me-2">Topics:</span>
            <?php foreach ($subCategories as $sub): ?>
            <a href="#" class="badge bg-light text-dark border px-3 py-2 text-decoration-none shadow-sm hover-lift rounded-pill"><?= e($sub['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

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
        </div>
        
        <div class="col-lg-4 mt-5 mt-lg-0">
            <?php require VIEWS_PATH . '/frontend/includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<style>
    .hover-lift { transition: transform 0.2s; }
    .hover-lift:hover { transform: translateY(-3px); }
</style>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
