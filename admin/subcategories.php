<?php
/**
 * Admin - Sub Categories CRUD
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('subcategories');

$pageTitle = 'Sub Categories';
$subCategoryModel = new SubCategoryModel();
$categoryModel = new CategoryModel();
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/subcategories.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $item = $subCategoryModel->findById($id);
        if ($item) {
            $subCategoryModel->delete($id);
            ActivityLogModel::log(Auth::id(), 'delete', 'subcategories', 'Deleted sub category: ' . $item['name'], $id);
            Session::flash('success', 'Sub category deleted.');
        }
        redirect(ADMIN_URL . '/subcategories.php');
    }

    $name = trim($_POST['name'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $name);
    $id = (int) ($_POST['id'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if (empty($name) || !$categoryId) {
        Session::flash('error', 'Name and category are required.');
        redirect(ADMIN_URL . '/subcategories.php' . ($id ? '?edit=' . $id : ''));
    }

    if ($subCategoryModel->slugExists($slug, $id ?: null)) {
        $slug = $slug . '-' . time();
    }

    $data = [
        'category_id'   => $categoryId,
        'name'          => Security::sanitize($name),
        'slug'          => $slug,
        'description'   => Security::sanitize($_POST['description'] ?? ''),
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'status'        => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];

    if ($id) {
        $subCategoryModel->update($id, $data);
        ActivityLogModel::log(Auth::id(), 'update', 'subcategories', 'Updated sub category: ' . $data['name'], $id);
        Session::flash('success', 'Sub category updated.');
    } else {
        $newId = $subCategoryModel->create($data);
        ActivityLogModel::log(Auth::id(), 'create', 'subcategories', 'Created sub category: ' . $data['name'], $newId);
        Session::flash('success', 'Sub category created.');
    }
    redirect(ADMIN_URL . '/subcategories.php');
}

if (!empty($_GET['edit'])) {
    $editItem = $subCategoryModel->findById((int) $_GET['edit']);
}

$categories = $categoryModel->getAll('active');
$subCategories = $subCategoryModel->getAll();

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">Sub Categories</h4>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3"><?= $editItem ? 'Edit Sub Category' : 'Add Sub Category' ?></h5>
                    <form method="POST">
                        <?= Security::csrfField() ?>
                        <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (int)($editItem['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="title" class="form-control" value="<?= e($editItem['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($editItem['slug'] ?? '') ?>" <?= $editItem ? 'data-manual="true"' : '' ?>>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"><?= e($editItem['description'] ?? '') ?></textarea>
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
                            <a href="<?= ADMIN_URL ?>/subcategories.php" class="btn btn-outline-secondary">Cancel</a>
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
                                <tr><th>ID</th><th>Name</th><th>Category</th><th>Slug</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subCategories as $sc): ?>
                                <tr>
                                    <td><?= $sc['id'] ?></td>
                                    <td><?= e($sc['name']) ?></td>
                                    <td><?= e($sc['category_name'] ?? '-') ?></td>
                                    <td><code><?= e($sc['slug']) ?></code></td>
                                    <td><?= $sc['display_order'] ?></td>
                                    <td><span class="badge bg-<?= $sc['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($sc['status']) ?></span></td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/subcategories.php?edit=<?= $sc['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $sc['id'] ?>">
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
