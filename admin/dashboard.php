<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

Auth::requirePermission('dashboard');

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

$newsModel = new NewsModel();
$categoryModel = new CategoryModel();
$userModel = new UserModel();
$commentModel = new CommentModel();
$reportModel = new ReportModel();
$activityModel = new ActivityLogModel();

$stats = [
    'total_news'       => $newsModel->countByStatus(),
    'published_news'   => $newsModel->countByStatus('published'),
    'draft_news'       => $newsModel->countByStatus('draft'),
    'categories'       => $categoryModel->count(),
    'users'            => $userModel->count(),
    'pending_comments' => count($commentModel->getAll(1, 'pending')['data']),
];

$visitorStats = $reportModel->getDashboardStats();
$chartData = $reportModel->getVisitorChart(30);
$pageViewChart = $reportModel->getPageViewChart(30);
$mostViewed = $newsModel->getMostViewed(10);
$recentActivities = $activityModel->getRecent(15);

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Dashboard</h1>
    <span class="text-muted">Welcome back, <?= e(Auth::user()['name'] ?? '') ?>!</span>
</div>

<?= renderFlash() ?>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total News</div>
                <div class="fs-3 fw-bold text-danger"><?= $stats['total_news'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Published</div>
                <div class="fs-3 fw-bold text-success"><?= $stats['published_news'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Drafts</div>
                <div class="fs-3 fw-bold text-warning"><?= $stats['draft_news'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Categories</div>
                <div class="fs-3 fw-bold text-primary"><?= $stats['categories'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Users</div>
                <div class="fs-3 fw-bold text-info"><?= $stats['users'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Pending Comments</div>
                <div class="fs-3 fw-bold text-secondary"><?= $stats['pending_comments'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Visitors</div>
                <div class="fs-4 fw-bold"><?= number_format($visitorStats['total_visitors']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Today</div>
                <div class="fs-4 fw-bold"><?= number_format($visitorStats['today_visitors']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">This Month</div>
                <div class="fs-4 fw-bold"><?= number_format($visitorStats['monthly_visitors']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Page Views</div>
                <div class="fs-4 fw-bold"><?= number_format($visitorStats['total_pageviews']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Visitor Analytics (30 Days)</h5></div>
            <div class="card-body">
                <canvas id="visitorChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Most Viewed News</h5></div>
            <div class="list-group list-group-flush">
                <?php if (empty($mostViewed)): ?>
                    <div class="list-group-item text-muted">No data yet</div>
                <?php else: ?>
                    <?php foreach ($mostViewed as $item): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="me-2">
                            <div class="fw-semibold small"><?= e(truncate($item['title'], 50)) ?></div>
                            <small class="text-muted"><?= e($item['category_name'] ?? 'Uncategorized') ?></small>
                        </div>
                        <span class="badge bg-danger"><?= number_format((int) $item['views']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white"><h5 class="mb-0">Recent Activity</h5></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>User</th><th>Action</th><th>Description</th><th>Time</th></tr></thead>
            <tbody>
                <?php if (empty($recentActivities)): ?>
                    <tr><td colspan="4" class="text-muted text-center">No activity yet</td></tr>
                <?php else: ?>
                    <?php foreach ($recentActivities as $log): ?>
                    <tr>
                        <td><?= e($log['user_name'] ?? 'System') ?></td>
                        <td><span class="badge bg-secondary"><?= e($log['action']) ?></span></td>
                        <td><?= e($log['description'] ?? '') ?></td>
                        <td><small class="text-muted"><?= e(timeAgo($log['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$chartLabels = json_encode(array_column($chartData, 'date'));
$chartVisitors = json_encode(array_column($chartData, 'visitors'));
$chartPageviews = json_encode(array_column($pageViewChart, 'pageviews'));
$extraScripts = <<<HTML
<script>
new Chart(document.getElementById('visitorChart'), {
    type: 'line',
    data: {
        labels: {$chartLabels},
        datasets: [
            { label: 'Visitors', data: {$chartVisitors}, borderColor: '#dc3545', tension: 0.3 },
            { label: 'Page Views', data: {$chartPageviews}, borderColor: '#0d6efd', tension: 0.3 }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
HTML;
require APP_ROOT . '/includes/footer.php';
?>
