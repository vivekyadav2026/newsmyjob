<?php
/**
 * Admin - Activity Logs
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('reports');

$pageTitle = 'Activity Logs';
$activityModel = new ActivityLogModel();

$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $activityModel->getAll($page, ADMIN_PER_PAGE);
$logs = $result['data'];
$total = $result['total'];

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Activity Logs</h4>
            <span class="text-muted"><?= number_format($total) ?> total entries</span>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td><?= e($log['user_name'] ?? 'System') ?></td>
                            <td>
                                <?php
                                $actionBadge = match ($log['action']) {
                                    'create' => 'success',
                                    'update' => 'primary',
                                    'delete' => 'danger',
                                    'login', 'logout' => 'info',
                                    default => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $actionBadge ?>"><?= e($log['action']) ?></span>
                            </td>
                            <td><?= e($log['description']) ?></td>
                            <td><code><?= e($log['ip_address'] ?? '-') ?></code></td>
                            <td><?= formatDateTime($log['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= renderPagination($total, $page, ADMIN_PER_PAGE, ADMIN_URL . '/activity-logs.php') ?>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
