<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('categories');

$pageTitle = 'Add Category';
$currentPage = 'categories';
$model = new CategoryModel();
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
        'status' => $_POST['status'] ?? 'active',
    ];
    if (empty($name)) $errors[] = 'Name is required.';
    if ($model->slugExists($slug)) $data['slug'] = uniqueSlug(Database::getInstance(), 'categories', $slug);
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadFile($_FILES['image'], 'categories', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) $data['image'] = $upload['path'];
        else $errors[] = $upload['message'];
    }
    if (empty($errors)) {
        $id = $model->create($data);
        logActivity('create', 'categories', $id, 'Created category: ' . $name);
        Session::flash('success', 'Category created successfully.');
        redirect(adminUrl('categories/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Category</h1>
    <a href="<?= adminUrl('categories/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" id="title" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Slug</label><input type="text" name="slug" id="slug" class="form-control"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                <div class="col-md-4"><label class="form-label">Icon (Bootstrap class)</label><input type="text" name="icon" class="form-control" placeholder="bi bi-folder"></div>
                <div class="col-md-4"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-md-6"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control"></div>
                <div class="col-12"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2"></textarea></div>
                <div class="col-12"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords" class="form-control"></div>
            </div>
            <button type="submit" class="btn btn-danger mt-3">Save Category</button>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
