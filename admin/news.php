<?php
/**
 * Admin - News List
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('news');

$pageTitle = 'News Articles';
$newsModel = new NewsModel();
$categoryModel = new CategoryModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $article = $newsModel->findById($id);
        if ($article) {
            if ($article['featured_image']) {
                deleteUploadedFile($article['featured_image']);
            }
            $newsModel->delete($id);
            ActivityLogModel::log(Auth::id(), 'delete', 'news', 'Deleted article: ' . $article['title'], $id);
            Session::flash('success', 'Article deleted successfully.');
        }
    }
    redirect(ADMIN_URL . '/news.php?' . http_build_query(array_filter([
        'status' => $_GET['status'] ?? '',
        'category_id' => $_GET['category_id'] ?? '',
        'search' => $_GET['search'] ?? '',
    ])));
}

$filters = [
    'status'      => Security::sanitize($_GET['status'] ?? ''),
    'category_id' => (int) ($_GET['category_id'] ?? 0) ?: null,
    'search'      => Security::sanitize($_GET['search'] ?? ''),
];
$filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);

$result = $newsModel->getAll($filters, 1, 500);
$articles = $result['data'];
$categories = $categoryModel->getAll();

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">News Articles</h4>
            <a href="<?= ADMIN_URL ?>/news-add.php" class="btn btn-danger"><i class="bi bi-plus-lg me-1"></i> Add News</a>
        </div>

        <div class="content-card mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <?php foreach (['draft', 'published', 'scheduled'] as $st): ?>
                        <option value="<?= $st ?>" <?= ($_GET['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (int)($_GET['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Search title or excerpt...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= $article['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($article['featured_image']): ?>
                                    <img src="<?= uploadUrl($article['featured_image']) ?>" alt="" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                    <?php endif; ?>
                                    <span><?= e(truncate($article['title'], 50)) ?></span>
                                    <?php if ($article['is_featured']): ?><span class="badge bg-warning text-dark">Featured</span><?php endif; ?>
                                    <?php if ($article['is_trending']): ?><span class="badge bg-info">Trending</span><?php endif; ?>
                                </div>
                            </td>
                            <td><?= e($article['category_name'] ?? '-') ?></td>
                            <td><?= e($article['author_name'] ?? '-') ?></td>
                            <td>
                                <?php
                                $badge = match ($article['status']) {
                                    'published' => 'success',
                                    'scheduled' => 'info',
                                    default => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($article['status']) ?></span>
                            </td>
                            <td><?= number_format($article['views']) ?></td>
                            <td><?= formatDateTime($article['created_at']) ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/news-edit.php?id=<?= $article['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if ($article['status'] === 'published'): ?>
                                <a href="<?= newsUrl($article['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                <?php endif; ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this article?');">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $article['id'] ?>">
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
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
