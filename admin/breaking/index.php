<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('breaking');

$pageTitle = 'Breaking News';
$currentPage = 'breaking';
$model = new BreakingNewsModel();
$items = $model->getAll();

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Breaking News</h1>
    <a href="<?= adminUrl('breaking/add.php') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Add Breaking News</a>
</div>

<?= renderFlash() ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Order</th><th>Title</th><th>Linked News</th><th>Expires</th><th>Status</th><th width="150">Actions</th></tr></thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No breaking news</td></tr>
                <?php else: foreach ($items as $item): ?>
                <tr>
                    <td><?= (int) $item['display_order'] ?></td>
                    <td class="fw-semibold"><?= e($item['title']) ?></td>
                    <td><?= e($item['news_title'] ?? ($item['link'] ? 'Custom Link' : '-')) ?></td>
                    <td><small><?= e(formatDateTime($item['expires_at'] ?? '')) ?: 'Never' ?></small></td>
                    <td>
                        <form method="POST" action="<?= adminUrl('breaking/toggle.php') ?>" class="d-inline">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-<?= $item['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($item['status']) ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="<?= adminUrl('breaking/edit.php?id=' . $item['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="<?= adminUrl('breaking/delete.php') ?>" class="d-inline" onsubmit="return confirm('Delete?')">
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
