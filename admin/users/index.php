<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('users');

$pageTitle = 'Users';
$currentPage = 'users';
$userModel = new UserModel();
$page = max(1, (int) ($_GET['page'] ?? 1));
$role = $_GET['role'] ?? '';
$result = $userModel->getAll($page, ADMIN_PER_PAGE, $role ?: null);
$baseUrl = adminUrl('users/index.php') . ($role ? '?role=' . urlencode($role) : '');

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Users</h1>
    <a href="<?= adminUrl('users/add.php') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Add User</a>
</div>

<?= renderFlash() ?>

<div class="btn-group mb-3">
    <a href="<?= adminUrl('users/index.php') ?>" class="btn btn-<?= !$role ? 'danger' : 'outline-secondary' ?>">All</a>
    <?php foreach (['super_admin', 'admin', 'editor', 'author'] as $r): ?>
    <a href="<?= adminUrl('users/index.php?role=' . $r) ?>" class="btn btn-<?= $role === $r ? 'danger' : 'outline-secondary' ?>"><?= ucfirst(str_replace('_', ' ', $r)) ?></a>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th width="120">Actions</th></tr></thead>
            <tbody>
                <?php if (empty($result['data'])): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No users found</td></tr>
                <?php else: foreach ($result['data'] as $u): ?>
                <tr>
                    <td class="fw-semibold"><?= e($u['name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $u['role'])) ?></span></td>
                    <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($u['status']) ?></span></td>
                    <td><small><?= e(formatDateTime($u['last_login'] ?? '')) ?: 'Never' ?></small></td>
                    <td>
                        <a href="<?= adminUrl('users/edit.php?id=' . $u['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if ($u['role'] !== 'super_admin' && (int) $u['id'] !== Auth::id()): ?>
                        <form method="POST" action="<?= adminUrl('users/delete.php') ?>" class="d-inline" onsubmit="return confirm('Delete user?')">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
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
