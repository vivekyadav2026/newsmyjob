<?php
/**
 * Search Results Page - Premium Redesign
 */
$keyword = Security::sanitize($_GET['q'] ?? '');
$categoryId = (int) ($_GET['category'] ?? 0);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));

$filters = array_filter([
    'category_id' => $categoryId ?: null,
    'date_from'   => $dateFrom,
    'date_to'     => $dateTo,
]);

$newsModel = new NewsModel();
$result = $newsModel->search($keyword, $filters, $page);
$categories = (new CategoryModel())->getAll('active');

$pageTitle = 'Search: ' . ($keyword ?: 'All') . ' - ' . setting('site_name');

require VIEWS_PATH . '/frontend/includes/header.php';
?>

<div class="container my-5">
    <div class="page-header mb-4 pb-3 border-bottom border-2" style="border-color: var(--primary-color) !important;">
        <h1 class="display-5 fw-bold font-merriweather text-dark mb-2"><i class="bi bi-search text-danger me-3"></i>Search Results</h1>
        <p class="fs-6 text-muted mb-0">Showing <?= $result['total'] ?> results for <strong>"<?= e($keyword ?: 'All') ?>"</strong></p>
    </div>

    <div class="row">
        <div class="col-lg-8">

            <form method="GET" class="bg-light p-4 rounded shadow-sm mb-5 border">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted text-uppercase">Search Keyword</label>
                        <input type="text" name="q" class="form-control form-control-lg" placeholder="Type here..." value="<?= e($keyword) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?= e($dateTo) ?>">
                    </div>
                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-bold"><i class="bi bi-funnel me-2"></i>Filter Results</button>
                    </div>
                </div>
            </form>

            <div class="d-flex flex-column gap-4">
                <?php if ($result['data']): ?>
                    <?php foreach ($result['data'] as $article): ?>
                        <?php include VIEWS_PATH . '/frontend/includes/news-card-hz.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-muted">No news found matching your criteria.</h4>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-5">
                <?= renderPagination($result['total'], $page, FRONTEND_PER_PAGE, BASE_URL . '/search?q=' . urlencode($keyword)) ?>
            </div>
        </div>
        <div class="col-lg-4 mt-5 mt-lg-0">
            <?php require VIEWS_PATH . '/frontend/includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
