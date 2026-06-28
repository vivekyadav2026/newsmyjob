<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('subcategories');

$pageTitle = 'Add Sub Category';
$currentPage = 'subcategories';
$model = new SubCategoryModel();
$categoryModel = new CategoryModel();
$categories = $categoryModel->getAll('active');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: generateSlug($name);
    $data = [
        'category_id' => (int) ($_POST['category_id'] ?? 0),
        'name' => $name, 'slug' => $slug,
        'description' => trim($_POST['description'] ?? ''),
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'status' => $_POST['status'] ?? 'active',
    ];
    if (empty($name)) $errors[] = 'Name is required.';
    if (!$data['category_id']) $errors[] = 'Category is required.';
    if ($model->slugExists($slug)) $data['slug'] = uniqueSlug(Database::getInstance(), 'sub_categories', $slug);
    if (empty($errors)) {
        $id = $model->create($data);
        logActivity('create', 'subcategories', $id, 'Created sub category: ' . $name);
        Session::flash('success', 'Sub category created.');
        redirect(adminUrl('subcategories/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Sub Category</h1>
    <a href="<?= adminUrl('subcategories/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>

<div class="card shadow-sm"><div class="card-body">
<form method="POST"><?= csrfField() ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Category *</label><select name="category_id" class="form-select" required><option value="">Select</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" id="title" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Slug</label><input type="text" name="slug" id="slug" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
</div>
<button type="submit" class="btn btn-danger mt-3">Save</button>
</form></div></div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
