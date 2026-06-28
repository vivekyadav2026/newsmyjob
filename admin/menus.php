<?php
/**
 * Admin - Menus CRUD
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('menus');

$pageTitle = 'Menus';
$menuModel = new MenuModel();
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/menus.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $item = $menuModel->findById($id);
        if ($item) {
            $menuModel->delete($id);
            ActivityLogModel::log(Auth::id(), 'delete', 'menus', 'Deleted menu: ' . $item['title'], $id);
            Session::flash('success', 'Menu item deleted.');
        }
        redirect(ADMIN_URL . '/menus.php');
    }

    $title = trim($_POST['title'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (empty($title) || empty($_POST['url'])) {
        Session::flash('error', 'Title and URL are required.');
        redirect(ADMIN_URL . '/menus.php' . ($id ? '?edit=' . $id : ''));
    }

    $data = [
        'title'         => Security::sanitize($title),
        'url'           => Security::sanitize($_POST['url']),
        'parent_id'     => (int) ($_POST['parent_id'] ?? 0) ?: null,
        'menu_location' => in_array($_POST['menu_location'] ?? '', ['header', 'footer'], true) ? $_POST['menu_location'] : 'header',
        'target'        => in_array($_POST['target'] ?? '', ['_self', '_blank'], true) ? $_POST['target'] : '_self',
        'icon'          => Security::sanitize($_POST['icon'] ?? ''),
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'status'        => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];

    if ($id) {
        $menuModel->update($id, $data);
        ActivityLogModel::log(Auth::id(), 'update', 'menus', 'Updated menu: ' . $data['title'], $id);
        Session::flash('success', 'Menu updated.');
    } else {
        $newId = $menuModel->create($data);
        ActivityLogModel::log(Auth::id(), 'create', 'menus', 'Created menu: ' . $data['title'], $newId);
        Session::flash('success', 'Menu created.');
    }
    redirect(ADMIN_URL . '/menus.php');
}

if (!empty($_GET['edit'])) {
    $editItem = $menuModel->findById((int) $_GET['edit']);
}

$menus = $menuModel->getAll();
$parentMenus = array_filter($menus, fn($m) => empty($m['parent_id']));

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">Navigation Menus</h4>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3"><?= $editItem ? 'Edit Menu' : 'Add Menu Item' ?></h5>
                    <form method="POST">
                        <?= Security::csrfField() ?>
                        <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= e($editItem['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL <span class="text-danger">*</span></label>
                            <input type="text" name="url" class="form-control" value="<?= e($editItem['url'] ?? '') ?>" placeholder="/category/politics" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select name="menu_location" class="form-select">
                                <option value="header" <?= ($editItem['menu_location'] ?? 'header') === 'header' ? 'selected' : '' ?>>Header</option>
                                <option value="footer" <?= ($editItem['menu_location'] ?? '') === 'footer' ? 'selected' : '' ?>>Footer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Menu</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Top Level)</option>
                                <?php foreach ($parentMenus as $pm): ?>
                                <?php if ($editItem && $pm['id'] == $editItem['id']) continue; ?>
                                <option value="<?= $pm['id'] ?>" <?= (int)($editItem['parent_id'] ?? 0) === (int)$pm['id'] ? 'selected' : '' ?>><?= e($pm['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" class="form-control" value="<?= e($editItem['icon'] ?? '') ?>" placeholder="bi-house">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target</label>
                            <select name="target" class="form-select">
                                <option value="_self" <?= ($editItem['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>Same Window</option>
                                <option value="_blank" <?= ($editItem['target'] ?? '') === '_blank' ? 'selected' : '' ?>>New Window</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="<?= e($editItem['display_order'] ?? '0') ?>" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($editItem['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($editItem['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger"><?= $editItem ? 'Update' : 'Create' ?></button>
                            <?php if ($editItem): ?>
                            <a href="<?= ADMIN_URL ?>/menus.php" class="btn btn-outline-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr><th>ID</th><th>Title</th><th>URL</th><th>Location</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menus as $menu): ?>
                                <tr>
                                    <td><?= $menu['id'] ?></td>
                                    <td><?= $menu['parent_id'] ? '— ' : '' ?><?= e($menu['title']) ?></td>
                                    <td><code><?= e(truncate($menu['url'], 30)) ?></code></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($menu['menu_location']) ?></span></td>
                                    <td><?= $menu['display_order'] ?></td>
                                    <td><span class="badge bg-<?= $menu['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($menu['status']) ?></span></td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/menus.php?edit=<?= $menu['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $menu['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
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
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
