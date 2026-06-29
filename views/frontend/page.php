<?php
/**
 * Custom Page Template (About/Privacy/Terms) - Premium UI
 */
$slug = $_GET['slug'] ?? '';
$pageModel = new PageModel();
$pageData = $pageModel->findBySlug($slug);

if (!$pageData || $pageData['status'] !== 'published') {
    http_response_code(404);
    require VIEWS_PATH . '/frontend/404.php';
    exit;
}

$pageTitle = $pageData['meta_title'] ?: $pageData['title'] . ' - ' . setting('site_name');
$metaDescription = $pageData['meta_description'];
$metaKeywords = $pageData['meta_keywords'];

trackPageView(null, 'page');

require VIEWS_PATH . '/frontend/includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb fw-semibold" style="font-size: 13px;">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-dark"><i class="bi bi-house-door-fill"></i> Home</a></li>
            <li class="breadcrumb-item active text-danger"><?= e($pageData['title']) ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            <div class="bg-white p-4 p-md-5 rounded shadow-sm border">
                <div class="page-header mb-4 pb-3 border-bottom border-2 text-center" style="border-color: var(--primary-color) !important;">
                    <h1 class="display-5 fw-bold font-merriweather text-dark mb-0"><?= e($pageData['title']) ?></h1>
                </div>
                
                <div class="article-content" style="font-size: 1.1rem; line-height: 1.8;">
                    <?= $pageData['content'] ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
