<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('reports');

$pageTitle = 'Visitor Reports';
$currentPage = 'reports';
$reportModel = new ReportModel();
$newsModel = new NewsModel();

$days = max(7, min(90, (int) ($_GET['days'] ?? 30)));
$stats = $reportModel->getDashboardStats();
$chartData = $reportModel->getVisitorChart($days);
$popularCategories = $reportModel->getPopularCategories(10);
$mostViewed = $newsModel->getMostViewed(15);

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Visitor Reports</h1>
    <form method="GET" class="d-flex gap-2">
        <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ([7, 14, 30, 60, 90] as $d): ?>
            <option value="<?= $d ?>" <?= $days === $d ? 'selected' : '' ?>><?= $d ?> Days</option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?= renderFlash() ?>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Total Visitors</div><div class="fs-3 fw-bold"><?= number_format($stats['total_visitors']) ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Today</div><div class="fs-3 fw-bold text-success"><?= number_format($stats['today_visitors']) ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">This Month</div><div class="fs-3 fw-bold text-primary"><?= number_format($stats['monthly_visitors']) ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Total Page Views</div><div class="fs-3 fw-bold text-danger"><?= number_format($stats['total_pageviews']) ?></div></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Traffic Chart (<?= $days ?> Days)</h5></div>
            <div class="card-body"><canvas id="trafficChart" height="100"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Popular Categories</h5></div>
            <div class="list-group list-group-flush">
                <?php if (empty($popularCategories)): ?>
                <div class="list-group-item text-muted">No data yet</div>
                <?php else: foreach ($popularCategories as $cat): ?>
                <div class="list-group-item d-flex justify-content-between"><span><?= e($cat['name']) ?></span><span class="badge bg-danger"><?= number_format((int)$cat['views']) ?></span></div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white"><h5 class="mb-0">Most Viewed Articles</h5></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Views</th><th>Published</th></tr></thead>
            <tbody>
                <?php foreach ($mostViewed as $i => $item): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e(truncate($item['title'], 60)) ?></td>
                    <td><?= e($item['category_name'] ?? '-') ?></td>
                    <td><span class="badge bg-danger"><?= number_format((int)$item['views']) ?></span></td>
                    <td><small><?= e(formatDate($item['published_at'] ?? $item['created_at'])) ?></small></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$labels = json_encode(array_column($chartData, 'date'));
$visitors = json_encode(array_column($chartData, 'visitors'));
$pageviews = json_encode(array_column($chartData, 'pageviews'));
$extraScripts = <<<HTML
<script>
new Chart(document.getElementById('trafficChart'), {
    type: 'bar',
    data: {
        labels: {$labels},
        datasets: [
            { label: 'Unique Visitors', data: {$visitors}, backgroundColor: 'rgba(220,53,69,0.7)' },
            { label: 'Page Views', data: {$pageviews}, backgroundColor: 'rgba(13,110,253,0.5)' }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
HTML;
require APP_ROOT . '/includes/footer.php';
?>
