<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('comments');

$pageTitle = 'Comments';
$currentPage = 'comments';
$commentModel = new CommentModel();
$page = max(1, (int) ($_GET['page'] ?? 1));
$status = $_GET['status'] ?? '';
$result = $commentModel->getAll($page, $status ?: null);
$baseUrl = adminUrl('comments/index.php') . ($status ? '?status=' . urlencode($status) : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $commentModel->updateStatus($id, 'approved');
        logActivity('approve', 'comments', $id, 'Approved comment');
        Session::flash('success', 'Comment approved.');
    } elseif ($action === 'reject') {
        $commentModel->updateStatus($id, 'rejected');
        logActivity('reject', 'comments', $id, 'Rejected comment');
        Session::flash('success', 'Comment rejected.');
    } elseif ($action === 'spam') {
        $commentModel->updateStatus($id, 'spam');
        Session::flash('success', 'Marked as spam.');
    } elseif ($action === 'delete') {
        $commentModel->delete($id);
        logActivity('delete', 'comments', $id, 'Deleted comment');
        Session::flash('success', 'Comment deleted.');
    }
    redirect($baseUrl . ($page > 1 ? (str_contains($baseUrl, '?') ? '&' : '?') . 'page=' . $page : ''));
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Comments Moderation</h1>
</div>

<?= renderFlash() ?>

<div class="btn-group mb-3">
    <a href="<?= adminUrl('comments/index.php') ?>" class="btn btn-<?= !$status ? 'danger' : 'outline-secondary' ?>">All</a>
    <?php foreach (['pending', 'approved', 'rejected', 'spam'] as $st): ?>
    <a href="<?= adminUrl('comments/index.php?status=' . $st) ?>" class="btn btn-<?= $status === $st ? 'danger' : 'outline-secondary' ?>"><?= ucfirst($st) ?></a>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Author</th><th>Comment</th><th>Article</th><th>Status</th><th>Date</th><th width="200">Actions</th></tr></thead>
            <tbody>
                <?php if (empty($result['data'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No comments found</td></tr>
                <?php else: foreach ($result['data'] as $c): ?>
                <tr>
                    <td><div class="fw-semibold"><?= e($c['name']) ?></div><small class="text-muted"><?= e($c['email']) ?></small></td>
                    <td><?= e(truncate($c['comment'], 100)) ?></td>
                    <td><small><?= e(truncate($c['news_title'] ?? 'Unknown', 40)) ?></small></td>
                    <td><span class="badge bg-<?= match($c['status']) { 'approved'=>'success','pending'=>'warning','spam'=>'dark',default=>'secondary' } ?>"><?= ucfirst($c['status']) ?></span></td>
                    <td><small><?= e(timeAgo($c['created_at'])) ?></small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <?php if ($c['status'] !== 'approved'): ?>
                            <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="approve"><button class="btn btn-success" title="Approve"><i class="bi bi-check"></i></button></form>
                            <?php endif; ?>
                            <?php if ($c['status'] !== 'rejected'): ?>
                            <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="reject"><button class="btn btn-warning" title="Reject"><i class="bi bi-x"></i></button></form>
                            <?php endif; ?>
                            <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="spam"><button class="btn btn-secondary" title="Spam"><i class="bi bi-exclamation-triangle"></i></button></form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')"><?= csrfField() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-danger" title="Delete"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($result['total'] > ADMIN_PER_PAGE): ?>
    <div class="card-footer"><?= renderPagination($result['total'], $page, ADMIN_PER_PAGE, $baseUrl) ?></div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
