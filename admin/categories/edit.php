<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('categories');

$id = (int) ($_GET['id'] ?? 0);
$model = new CategoryModel();
$cat = $model->findById($id);
if (!$cat) { Session::flash('error', 'Category not found.'); redirect(adminUrl('categories/index.php')); }

$pageTitle = 'Edit Category';
$currentPage = 'categories';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: generateSlug($name);
    $data = [
        'name' => $name, 'slug' => $slug,
        'description' => trim($_POST['description'] ?? ''),
        'icon' => trim($_POST['icon'] ?? ''),
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'show_in_menu' => isset($_POST['show_in_menu']) ? 1 : 0,
        'show_on_home' => isset($_POST['show_on_home']) ? 1 : 0,
        'status' => $_POST['status'] ?? 'active',
        'image' => $cat['image'],
    ];
    if (empty($name)) $errors[] = 'Name is required.';
    if ($model->slugExists($slug, $id)) $data['slug'] = uniqueSlug(Database::getInstance(), 'categories', $slug, $id);
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadFile($_FILES['image'], 'categories', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            if ($cat['image']) deleteUploadedFile($cat['image']);
            $data['image'] = $upload['path'];
        } else $errors[] = $upload['message'];
    }
    if (empty($errors)) {
        $model->update($id, $data);
        logActivity('update', 'categories', $id, 'Updated category: ' . $name);
        Session::flash('success', 'Category updated successfully.');
        redirect(adminUrl('categories/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Category</h1>
    <a href="<?= adminUrl('categories/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?= renderFlash() ?>
<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" id="title" class="form-control" value="<?= e($cat['name']) ?>" required></div>
                <div class="col-md-6"><label class="form-label">Slug</label><input type="text" name="slug" id="slug" class="form-control" value="<?= e($cat['slug']) ?>" data-manual="true"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($cat['description'] ?? '') ?></textarea></div>
                <div class="col-md-4"><label class="form-label">Icon</label><input type="text" name="icon" class="form-control" value="<?= e($cat['icon'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="<?= (int) $cat['display_order'] ?>"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= $cat['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $cat['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                <div class="col-md-6"><label class="form-label">Image</label><?php if ($cat['image']): ?><img src="<?= uploadUrl($cat['image']) ?>" class="d-block mb-2 rounded" width="80"><?php endif; ?><input type="file" name="image" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="<?= e($cat['meta_title'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"><?= e($cat['meta_description'] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords" class="form-control" value="<?= e($cat['meta_keywords'] ?? '') ?>"></div>
                <div class="col-md-6 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="show_in_menu" name="show_in_menu" value="1" <?= (!empty($cat['show_in_menu'])) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="show_in_menu">Show in Header Menu</label>
                        <div class="form-text">If checked, this category and its active subcategories will appear in the main navigation bar.</div>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="show_on_home" name="show_on_home" value="1" <?= (!empty($cat['show_on_home'])) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="show_on_home">Show on Home Page</label>
                        <div class="form-text">If checked, the latest news from this category will be featured on the homepage.</div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-danger mt-3">Update Category</button>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
