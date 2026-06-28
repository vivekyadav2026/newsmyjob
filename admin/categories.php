<?php
/**
 * Admin - Categories CRUD
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('categories');

$pageTitle = 'Categories';
$categoryModel = new CategoryModel();
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/categories.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $item = $categoryModel->findById($id);
        if ($item) {
            if ($item['image']) {
                deleteUploadedFile($item['image']);
            }
            $categoryModel->delete($id);
            ActivityLogModel::log(Auth::id(), 'delete', 'categories', 'Deleted category: ' . $item['name'], $id);
            Session::flash('success', 'Category deleted.');
        }
        redirect(ADMIN_URL . '/categories.php');
    }

    $name = trim($_POST['name'] ?? '');
    $slug = generateSlug($_POST['slug'] ?? $name);
    $id = (int) ($_POST['id'] ?? 0);

    if (empty($name)) {
        Session::flash('error', 'Category name is required.');
        redirect(ADMIN_URL . '/categories.php' . ($id ? '?edit=' . $id : ''));
    }

    if ($categoryModel->slugExists($slug, $id ?: null)) {
        $slug = $slug . '-' . time();
    }

    $data = [
        'name'             => Security::sanitize($name),
        'slug'             => $slug,
        'description'      => Security::sanitize($_POST['description'] ?? ''),
        'icon'             => Security::sanitize($_POST['icon'] ?? ''),
        'meta_title'       => Security::sanitize($_POST['meta_title'] ?? ''),
        'meta_description' => Security::sanitize($_POST['meta_description'] ?? ''),
        'meta_keywords'    => Security::sanitize($_POST['meta_keywords'] ?? ''),
        'display_order'    => (int) ($_POST['display_order'] ?? 0),
        'status'           => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
        'image'            => null,
    ];

    if ($id) {
        $existing = $categoryModel->findById($id);
        $data['image'] = $existing['image'] ?? null;
    }

    if (!empty($_FILES['image']['name'])) {
        $upload = uploadFile($_FILES['image'], 'categories', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            if ($id && !empty($data['image'])) {
                deleteUploadedFile($data['image']);
            }
            $data['image'] = $upload['path'];
        } else {
            Session::flash('error', $upload['message']);
            redirect(ADMIN_URL . '/categories.php' . ($id ? '?edit=' . $id : ''));
        }
    }

    if ($id) {
        $categoryModel->update($id, $data);
        ActivityLogModel::log(Auth::id(), 'update', 'categories', 'Updated category: ' . $data['name'], $id);
        Session::flash('success', 'Category updated.');
    } else {
        $newId = $categoryModel->create($data);
        ActivityLogModel::log(Auth::id(), 'create', 'categories', 'Created category: ' . $data['name'], $newId);
        Session::flash('success', 'Category created.');
    }
    redirect(ADMIN_URL . '/categories.php');
}

if (!empty($_GET['edit'])) {
    $editItem = $categoryModel->findById((int) $_GET['edit']);
}

$categories = $categoryModel->getAll();

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Categories</h4>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3"><?= $editItem ? 'Edit Category' : 'Add Category' ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <?= Security::csrfField() ?>
                        <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
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
                            <textarea name="description" class="form-control" rows="3"><?= e($editItem['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (Bootstrap class)</label>
                            <input type="text" name="icon" class="form-control" value="<?= e($editItem['icon'] ?? '') ?>" placeholder="bi-folder">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <?php if (!empty($editItem['image'])): ?>
                            <div class="mb-2"><img src="<?= uploadUrl($editItem['image']) ?>" alt="" class="img-thumbnail" style="max-height:80px;"></div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
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
                            <a href="<?= ADMIN_URL ?>/categories.php" class="btn btn-outline-secondary">Cancel</a>
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
                                <tr><th>ID</th><th>Image</th><th>Name</th><th>Slug</th><th>News</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td>
                                        <?php if ($cat['image']): ?>
                                        <img src="<?= uploadUrl($cat['image']) ?>" alt="" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                        <?php else: ?>
                                        <i class="bi <?= e($cat['icon'] ?? 'bi-folder') ?> fs-4"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($cat['name']) ?></td>
                                    <td><code><?= e($cat['slug']) ?></code></td>
                                    <td><?= $cat['news_count'] ?? 0 ?></td>
                                    <td><?= $cat['display_order'] ?></td>
                                    <td><span class="badge bg-<?= $cat['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($cat['status']) ?></span></td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
