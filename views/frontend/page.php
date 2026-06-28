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
                <!-- Premium Page Banner -->
                <div class="bg-dark text-white p-4 p-md-5 rounded shadow-sm mb-5 position-relative overflow-hidden" style="border-left: 5px solid var(--primary-color) !important; margin: -3rem -3rem 3rem -3rem;">
                    <div class="position-relative z-1" style="z-index: 2;">
                        <h1 class="display-4 fw-bold font-merriweather mb-0"><?= e($pageData['title']) ?></h1>
                    </div>
                    <i class="bi bi-journal-text position-absolute text-white opacity-10" style="font-size: 15rem; right: -2rem; top: -3rem; transform: rotate(-15deg); z-index: 1;"></i>
                </div>
                
                <div class="article-content" style="font-size: 1.1rem; line-height: 1.8;">
                    <?= $pageData['content'] ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
