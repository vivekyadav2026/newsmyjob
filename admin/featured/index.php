<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('featured');

$pageTitle = 'Featured News';
$currentPage = 'featured';
$newsModel = new NewsModel();
$featured = $newsModel->getFeatured(50);
$allPublished = $newsModel->getAll(['status' => 'published'], 1, 200);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $newsId = (int) ($_POST['news_id'] ?? 0);
        $news = $newsModel->findById($newsId);
        if ($news) {
            $order = (int) ($_POST['featured_order'] ?? 0);
            $newsModel->update($newsId, array_merge($news, ['is_featured' => 1, 'featured_order' => $order]));
            logActivity('update', 'featured', $newsId, 'Added to featured');
            Session::flash('success', 'Added to featured news.');
        }
    } elseif ($action === 'remove') {
        $newsId = (int) ($_POST['news_id'] ?? 0);
        $news = $newsModel->findById($newsId);
        if ($news) {
            $newsModel->update($newsId, array_merge($news, ['is_featured' => 0, 'featured_order' => 0]));
            logActivity('update', 'featured', $newsId, 'Removed from featured');
            Session::flash('success', 'Removed from featured.');
        }
    } elseif ($action === 'reorder') {
        foreach ($_POST['order'] ?? [] as $newsId => $order) {
            $news = $newsModel->findById((int) $newsId);
            if ($news && $news['is_featured']) {
                $newsModel->update((int) $newsId, array_merge($news, ['featured_order' => (int) $order]));
            }
        }
        logActivity('update', 'featured', null, 'Reordered featured news');
        Session::flash('success', 'Featured order updated.');
    }
    redirect(adminUrl('featured/index.php'));
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Featured News</h1>
</div>

<?= renderFlash() ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Current Featured (drag order via numbers)</h5></div>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="reorder">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Order</th><th>Title</th><th>Category</th><th>Views</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if (empty($featured)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No featured news yet</td></tr>
                            <?php else: foreach ($featured as $item): ?>
                            <tr>
                                <td width="80"><input type="number" name="order[<?= $item['id'] ?>]" class="form-control form-control-sm" value="<?= (int) $item['featured_order'] ?>" min="0"></td>
                                <td><?= e(truncate($item['title'], 60)) ?></td>
                                <td><?= e($item['category_name'] ?? '-') ?></td>
                                <td><?= number_format((int) $item['views']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($featured): ?><div class="card-footer"><button type="submit" class="btn btn-primary btn-sm">Save Order</button></div><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Add to Featured</h5></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Select News</label>
                        <select name="news_id" class="form-select" required>
                            <option value="">Choose article...</option>
                            <?php foreach ($allPublished['data'] as $n): if ($n['is_featured']) continue; ?>
                            <option value="<?= $n['id'] ?>"><?= e(truncate($n['title'], 50)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="featured_order" class="form-control" value="0" min="0">
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Add Featured</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
