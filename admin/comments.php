<?php
/**
 * Admin - Comments Management
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('comments');

$pageTitle = 'Comments';
$commentModel = new CommentModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/comments.php');
    }

    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'approve') {
        $commentModel->updateStatus($id, 'approved');
        ActivityLogModel::log(Auth::id(), 'update', 'comments', 'Approved comment', $id);
        Session::flash('success', 'Comment approved.');
    } elseif ($action === 'reject') {
        $commentModel->updateStatus($id, 'spam');
        ActivityLogModel::log(Auth::id(), 'update', 'comments', 'Rejected comment', $id);
        Session::flash('success', 'Comment rejected.');
    } elseif ($action === 'delete') {
        $commentModel->delete($id);
        ActivityLogModel::log(Auth::id(), 'delete', 'comments', 'Deleted comment', $id);
        Session::flash('success', 'Comment deleted.');
    }

    redirect(ADMIN_URL . '/comments.php?' . http_build_query(array_filter(['status' => $_GET['status'] ?? ''])));
}

$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $commentModel->getAll($page, $statusFilter ?: null);
$comments = $result['data'];
$total = $result['total'];

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Comments</h4>
            <div class="btn-group">
                <a href="<?= ADMIN_URL ?>/comments.php" class="btn btn-sm btn-<?= !$statusFilter ? 'danger' : 'outline-secondary' ?>">All</a>
                <a href="<?= ADMIN_URL ?>/comments.php?status=pending" class="btn btn-sm btn-<?= $statusFilter === 'pending' ? 'danger' : 'outline-secondary' ?>">Pending</a>
                <a href="<?= ADMIN_URL ?>/comments.php?status=approved" class="btn btn-sm btn-<?= $statusFilter === 'approved' ? 'danger' : 'outline-secondary' ?>">Approved</a>
                <a href="<?= ADMIN_URL ?>/comments.php?status=spam" class="btn btn-sm btn-<?= $statusFilter === 'spam' ? 'danger' : 'outline-secondary' ?>">Spam</a>
            </div>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr><th>ID</th><th>Author</th><th>Comment</th><th>Article</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td>
                                <strong><?= e($c['name']) ?></strong><br>
                                <small class="text-muted"><?= e($c['email']) ?></small>
                            </td>
                            <td><?= e(truncate($c['comment'], 80)) ?></td>
                            <td><?= e(truncate($c['news_title'] ?? '-', 40)) ?></td>
                            <td>
                                <?php
                                $badge = match ($c['status']) {
                                    'approved' => 'success',
                                    'spam' => 'danger',
                                    default => 'warning',
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($c['status']) ?></span>
                            </td>
                            <td><?= formatDateTime($c['created_at']) ?></td>
                            <td>
                                <?php if ($c['status'] !== 'approved'): ?>
                                <form method="POST" class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <?php endif; ?>
                                <?php if ($c['status'] !== 'spam'): ?>
                                <form method="POST" class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject"><i class="bi bi-x-lg"></i></button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= renderPagination($total, $page, ADMIN_PER_PAGE, ADMIN_URL . '/comments.php?' . http_build_query(array_filter(['status' => $statusFilter]))) ?>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
