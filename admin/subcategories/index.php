<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('subcategories');

$pageTitle = 'Sub Categories';
$currentPage = 'subcategories';
$model = new SubCategoryModel();
$items = $model->getAll();

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Sub Categories</h1>
    <a href="<?= adminUrl('subcategories/add.php') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Add Sub Category</a>
</div>

<?= renderFlash() ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Order</th><th>Name</th><th>Category</th><th>Slug</th><th>Status</th><th width="120">Actions</th></tr></thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No sub categories found</td></tr>
                <?php else: foreach ($items as $item): ?>
                <tr>
                    <td><?= (int) $item['display_order'] ?></td>
                    <td class="fw-semibold"><?= e($item['name']) ?></td>
                    <td><?= e($item['category_name'] ?? '-') ?></td>
                    <td><code><?= e($item['slug']) ?></code></td>
                    <td><span class="badge bg-<?= $item['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($item['status']) ?></span></td>
                    <td>
                        <a href="<?= adminUrl('subcategories/edit.php?id=' . $item['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="<?= adminUrl('subcategories/delete.php') ?>" class="d-inline" onsubmit="return confirm('Delete?')">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= $item['id'] ?>">
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
