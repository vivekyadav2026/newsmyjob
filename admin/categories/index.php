<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('categories');

$pageTitle = 'Categories';
$currentPage = 'categories';
$model = new CategoryModel();
$categories = $model->getAll();

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Categories</h1>
    <a href="<?= adminUrl('categories/add.php') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Add Category</a>
</div>

<?= renderFlash() ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Order</th><th>Name</th><th>Slug</th><th>News</th><th>Status</th><th width="120">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No categories found</td></tr>
                <?php else: foreach ($categories as $cat): ?>
                <tr>
                    <td><?= (int) $cat['display_order'] ?></td>
                    <td class="fw-semibold"><?= e($cat['name']) ?></td>
                    <td><code><?= e($cat['slug']) ?></code></td>
                    <td><?= (int) ($cat['news_count'] ?? 0) ?></td>
                    <td><span class="badge bg-<?= $cat['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($cat['status']) ?></span></td>
                    <td>
                        <a href="<?= adminUrl('categories/edit.php?id=' . $cat['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="<?= adminUrl('categories/delete.php') ?>" class="d-inline" onsubmit="return confirm('Delete this category?')">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
