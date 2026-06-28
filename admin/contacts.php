<?php
/**
 * Admin - Contact Messages
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('contacts');

$pageTitle = 'Contact Messages';
$contactModel = new ContactModel();
$viewMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/contacts.php');
    }

    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'read') {
        $contactModel->markAsRead($id);
        ActivityLogModel::log(Auth::id(), 'update', 'contacts', 'Marked message as read', $id);
        Session::flash('success', 'Message marked as read.');
    } elseif ($action === 'delete') {
        $contactModel->delete($id);
        ActivityLogModel::log(Auth::id(), 'delete', 'contacts', 'Deleted contact message', $id);
        Session::flash('success', 'Message deleted.');
    }

    redirect(ADMIN_URL . '/contacts.php');
}

if (!empty($_GET['view'])) {
    $viewId = (int) $_GET['view'];
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $result = $contactModel->getAll($page, ADMIN_PER_PAGE);
    foreach ($result['data'] as $msg) {
        if ((int) $msg['id'] === $viewId) {
            $viewMessage = $msg;
            if (empty($msg['is_read'])) {
                $contactModel->markAsRead($viewId);
            }
            break;
        }
    }
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $contactModel->getAll($page, ADMIN_PER_PAGE);
$messages = $result['data'];
$total = $result['total'];
$unreadCount = $contactModel->countUnread();

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Contact Messages</h4>
            <?php if ($unreadCount > 0): ?>
            <span class="badge bg-danger"><?= $unreadCount ?> Unread</span>
            <?php endif; ?>
        </div>

        <?php if ($viewMessage): ?>
        <div class="content-card mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5><?= e($viewMessage['subject']) ?></h5>
                    <p class="text-muted mb-0">From: <?= e($viewMessage['name']) ?> &lt;<?= e($viewMessage['email']) ?>&gt;</p>
                    <?php if (!empty($viewMessage['phone'])): ?>
                    <p class="text-muted mb-0">Phone: <?= e($viewMessage['phone']) ?></p>
                    <?php endif; ?>
                    <small class="text-muted"><?= formatDateTime($viewMessage['created_at']) ?></small>
                </div>
                <a href="<?= ADMIN_URL ?>/contacts.php" class="btn btn-sm btn-outline-secondary">Close</a>
            </div>
            <hr>
            <p class="mb-0"><?= nl2br(e($viewMessage['message'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                        <tr class="<?= empty($msg['is_read']) ? 'table-warning' : '' ?>">
                            <td><?= $msg['id'] ?></td>
                            <td><?= e($msg['name']) ?></td>
                            <td><?= e($msg['email']) ?></td>
                            <td><?= e(truncate($msg['subject'], 40)) ?></td>
                            <td>
                                <?php if (!empty($msg['is_read'])): ?>
                                <span class="badge bg-success">Read</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark">Unread</span>
                                <?php endif; ?>
                            </td>
                            <td><?= formatDateTime($msg['created_at']) ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/contacts.php?view=<?= $msg['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <?php if (empty($msg['is_read'])): ?>
                                <form method="POST" class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="read">
                                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark Read"><i class="bi bi-envelope-open"></i></button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= renderPagination($total, $page, ADMIN_PER_PAGE, ADMIN_URL . '/contacts.php') ?>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
