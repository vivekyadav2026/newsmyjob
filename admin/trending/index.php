<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('trending');

$pageTitle = 'Trending News';
$currentPage = 'trending';
$newsModel = new NewsModel();
$manualTrending = $newsModel->getAll(['status' => 'published', 'is_trending' => 1], 1, 50);
$autoTrending = $newsModel->getMostViewed(20);
$allPublished = $newsModel->getAll(['status' => 'published'], 1, 200);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';
    $newsId = (int) ($_POST['news_id'] ?? 0);
    $news = $newsModel->findById($newsId);

    if ($news) {
        if ($action === 'add') {
            $newsModel->update($newsId, array_merge($news, ['is_trending' => 1]));
            logActivity('update', 'trending', $newsId, 'Marked as trending');
            Session::flash('success', 'Added to manual trending.');
        } elseif ($action === 'remove') {
            $newsModel->update($newsId, array_merge($news, ['is_trending' => 0]));
            logActivity('update', 'trending', $newsId, 'Removed from trending');
            Session::flash('success', 'Removed from manual trending.');
        }
    }
    redirect(adminUrl('trending/index.php'));
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Trending News</h1>
</div>

<?= renderFlash() ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Manual Trending</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Title</th><th>Views</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($manualTrending['data'])): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">None set manually</td></tr>
                        <?php else: foreach ($manualTrending['data'] as $item): ?>
                        <tr>
                            <td><?= e(truncate($item['title'], 50)) ?></td>
                            <td><?= number_format((int) $item['views']) ?></td>
                            <td>
                                <form method="POST" class="d-inline"><?= csrfField() ?>
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
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Add Manual Trending</h5></div>
            <div class="card-body">
                <form method="POST"><?= csrfField() ?>
                    <input type="hidden" name="action" value="add">
                    <select name="news_id" class="form-select mb-3" required>
                        <option value="">Select article...</option>
                        <?php foreach ($allPublished['data'] as $n): if ($n['is_trending']) continue; ?>
                        <option value="<?= $n['id'] ?>"><?= e(truncate($n['title'], 50)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-danger w-100">Add Trending</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Auto Trending (by Views)</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>Title</th><th>Views</th><th>Manual</th></tr></thead>
                    <tbody>
                        <?php foreach ($autoTrending as $i => $item): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e(truncate($item['title'], 50)) ?></td>
                            <td><span class="badge bg-danger"><?= number_format((int) $item['views']) ?></span></td>
                            <td><?= $item['is_trending'] ? '<span class="badge bg-info">Yes</span>' : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">Articles with highest views appear automatically on the frontend trending section.</div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
