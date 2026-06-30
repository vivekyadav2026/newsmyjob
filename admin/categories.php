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
        'parent_id'        => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
        'show_in_menu'     => isset($_POST['show_in_menu']) ? 1 : 0,
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
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- None (Top Level) --</option>
                                <?php foreach ($categories as $pCat): ?>
                                    <?php if ($pCat['parent_id'] === null && (!isset($editItem) || $pCat['id'] !== $editItem['id'])): ?>
                                        <option value="<?= $pCat['id'] ?>" <?= (($editItem['parent_id'] ?? '') == $pCat['id']) ? 'selected' : '' ?>>
                                            <?= e($pCat['name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Select a parent to make this a subcategory.</div>
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
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_in_menu" name="show_in_menu" value="1" <?= (!empty($editItem['show_in_menu'])) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="show_in_menu">Show in Header Menu</label>
                            <div class="form-text">If checked, this category will appear in the main navigation bar.</div>
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
                                    <td>
                                        <?php if ($cat['parent_id']): ?>
                                            <span class="ms-3 text-muted">↳</span> <?= e($cat['name']) ?>
                                            <br><small class="text-muted ms-4">Parent: <?= e($cat['parent_name']) ?></small>
                                        <?php else: ?>
                                            <strong><?= e($cat['name']) ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= e($cat['slug']) ?></code></td>
                                    <td><?= $cat['news_count'] ?? 0 ?></td>
                                    <td><?= $cat['display_order'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $cat['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($cat['status']) ?></span>
                                        <?php if ($cat['show_in_menu']): ?>
                                            <span class="badge bg-info mt-1 d-block"><i class="bi bi-menu-button"></i> Menu</span>
                                        <?php endif; ?>
                                    </td>
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
