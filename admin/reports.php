<?php
/**
 * Admin - Analytics Reports
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('reports');

$pageTitle = 'Reports & Analytics';
$reportModel = new ReportModel();
$newsModel = new NewsModel();
$newsletterModel = new NewsletterModel();
$commentModel = new CommentModel();

$days = max(7, min(90, (int) ($_GET['days'] ?? 30)));
$stats = $reportModel->getDashboardStats();
$chartData = $reportModel->getVisitorChart($days);
$popularCategories = $reportModel->getPopularCategories(8);
$mostViewed = $newsModel->getMostViewed(10);
$topAuthors = (new UserModel())->getTopAuthors(5);

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Reports & Analytics</h4>
            <form method="GET" class="d-flex gap-2">
                <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ([7, 14, 30, 60, 90] as $d): ?>
                    <option value="<?= $d ?>" <?= $days === $d ? 'selected' : '' ?>><?= $d ?> Days</option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($stats['total_visitors']) ?></h3>
                        <small class="text-muted">Total Unique Visitors</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($stats['today_visitors']) ?></h3>
                        <small class="text-muted">Today's Visitors</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($stats['monthly_visitors']) ?></h3>
                        <small class="text-muted">Monthly Visitors</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($stats['total_pageviews']) ?></h3>
                        <small class="text-muted">Total Page Views</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="content-card">
                    <h5 class="mb-3">Visitor Trends (Last <?= $days ?> Days)</h5>
                    <canvas id="visitorChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3">Popular Categories</h5>
                    <canvas id="categoryChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="content-card">
                    <h5 class="mb-3">Most Viewed Articles</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Title</th><th>Views</th></tr></thead>
                            <tbody>
                                <?php foreach ($mostViewed as $article): ?>
                                <tr>
                                    <td><?= e(truncate($article['title'], 45)) ?></td>
                                    <td><span class="badge bg-primary"><?= number_format($article['views']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="content-card">
                    <h5 class="mb-3">Top Authors</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Author</th><th>Articles</th><th>Total Views</th></tr></thead>
                            <tbody>
                                <?php foreach ($topAuthors as $author): ?>
                                <tr>
                                    <td><?= e($author['name']) ?></td>
                                    <td><?= $author['article_count'] ?></td>
                                    <td><?= number_format($author['total_views'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$chartLabels = json_encode(array_column($chartData, 'date'));
$chartVisitors = json_encode(array_column($chartData, 'visitors'));
$chartPageviews = json_encode(array_column($chartData, 'pageviews'));
$catLabels = json_encode(array_column($popularCategories, 'name'));
$catViews = json_encode(array_column($popularCategories, 'views'));
$extraScripts = <<<JS
<script>
const ctx1 = document.getElementById('visitorChart');
if (ctx1) {
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: {$chartLabels},
            datasets: [
                { label: 'Visitors', data: {$chartVisitors}, borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,0.1)', fill: true, tension: 0.4 },
                { label: 'Page Views', data: {$chartPageviews}, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.1)', fill: true, tension: 0.4 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}
const ctx2 = document.getElementById('categoryChart');
if (ctx2 && {$catLabels}.length) {
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: {$catLabels},
            datasets: [{ data: {$catViews}, backgroundColor: ['#dc3545','#0d6efd','#198754','#ffc107','#6f42c1','#fd7e14','#20c997','#6c757d'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}
</script>
JS;
require VIEWS_PATH . '/admin/includes/footer.php';
?>
