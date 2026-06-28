<?php
/**
 * Admin - Newsletter Subscribers
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('newsletter');

$pageTitle = 'Newsletter';
$newsletterModel = new NewsletterModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/newsletter.php');
    }

    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $newsletterModel->delete($id);
        ActivityLogModel::log(Auth::id(), 'delete', 'newsletter', 'Deleted subscriber', $id);
        Session::flash('success', 'Subscriber removed.');
    }
    redirect(ADMIN_URL . '/newsletter.php');
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $newsletterModel->getAll($page, ADMIN_PER_PAGE);
$subscribers = $result['data'];
$total = $result['total'];
$activeCount = $newsletterModel->countActive();

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Newsletter Subscribers</h4>
            <span class="badge bg-success fs-6"><?= $activeCount ?> Active</span>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr><th>ID</th><th>Email</th><th>Name</th><th>Status</th><th>Subscribed</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $sub): ?>
                        <tr>
                            <td><?= $sub['id'] ?></td>
                            <td><?= e($sub['email']) ?></td>
                            <td><?= e($sub['name'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= ($sub['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($sub['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td><?= formatDateTime($sub['subscribed_at'] ?? $sub['created_at'] ?? '') ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= renderPagination($total, $page, ADMIN_PER_PAGE, ADMIN_URL . '/newsletter.php') ?>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
