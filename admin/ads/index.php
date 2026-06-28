<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('ads');

$pageTitle = 'Advertisements';
$currentPage = 'ads';
$model = new AdvertisementModel();
$ads = $model->getAll();

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Advertisements</h1>
    <a href="<?= adminUrl('ads/add.php') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Add Ad</a>
</div>

<?= renderFlash() ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Title</th><th>Type</th><th>Position</th><th>Clicks</th><th>Impressions</th><th>Status</th><th width="120">Actions</th></tr></thead>
            <tbody>
                <?php if (empty($ads)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No advertisements</td></tr>
                <?php else: foreach ($ads as $ad): ?>
                <tr>
                    <td class="fw-semibold"><?= e($ad['title']) ?></td>
                    <td><?= e($ad['ad_type']) ?></td>
                    <td><?= e($ad['position']) ?></td>
                    <td><?= number_format((int) $ad['clicks']) ?></td>
                    <td><?= number_format((int) $ad['impressions']) ?></td>
                    <td><span class="badge bg-<?= $ad['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($ad['status']) ?></span></td>
                    <td>
                        <a href="<?= adminUrl('ads/edit.php?id=' . $ad['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="<?= adminUrl('ads/delete.php') ?>" class="d-inline" onsubmit="return confirm('Delete?')">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= $ad['id'] ?>">
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
