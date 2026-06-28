<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('news');

$pageTitle = 'News Articles';
$currentPage = 'news';

$newsModel = new NewsModel();
$categoryModel = new CategoryModel();

$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = [
    'status'      => $_GET['status'] ?? '',
    'category_id' => $_GET['category_id'] ?? '',
    'search'      => trim($_GET['search'] ?? ''),
];

if (Auth::role() === 'author') {
    $filters['author_id'] = Auth::id();
}

$cleanFilters = array_filter($filters, fn($v) => $v !== '' && $v !== null);
$result = $newsModel->getAll($cleanFilters, $page, ADMIN_PER_PAGE);
$categories = $categoryModel->getAll('active');

$queryParams = http_build_query(array_filter([
    'status' => $filters['status'] ?: null,
    'category_id' => $filters['category_id'] ?: null,
    'search' => $filters['search'] ?: null,
]));
$baseUrl = adminUrl('news/index.php') . ($queryParams ? '?' . $queryParams : '');

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">News Articles</h1>
    <a href="<?= adminUrl('news/add.php') ?>" class="btn btn-danger"><i class="bi bi-plus-lg"></i> Add News</a>
</div>

<?= renderFlash() ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="<?= e($filters['search']) ?>" placeholder="Title or excerpt...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php foreach (['draft', 'published', 'scheduled', 'archived'] as $st): ?>
                    <option value="<?= $st ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (string) $filters['category_id'] === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= adminUrl('news/index.php') ?>" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['data'])): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No news articles found</td></tr>
                <?php else: ?>
                <?php foreach ($result['data'] as $item): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($item['featured_image']): ?>
                            <img src="<?= uploadUrl($item['featured_image']) ?>" width="40" height="40" class="rounded" style="object-fit:cover;">
                            <?php endif; ?>
                            <div>
                                <div class="fw-semibold"><?= e(truncate($item['title'], 60)) ?></div>
                                <?php if ($item['is_featured']): ?><span class="badge bg-warning text-dark">Featured</span><?php endif; ?>
                                <?php if ($item['is_trending']): ?><span class="badge bg-info">Trending</span><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?= e($item['category_name'] ?? '-') ?></td>
                    <td><?= e($item['author_name'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= match($item['status']) { 'published' => 'success', 'draft' => 'secondary', 'scheduled' => 'warning', default => 'dark' } ?>"><?= ucfirst($item['status']) ?></span></td>
                    <td><?= number_format((int) $item['views']) ?></td>
                    <td><small><?= e(formatDateTime($item['published_at'] ?? $item['created_at'])) ?></small></td>
                    <td>
                        <a href="<?= adminUrl('news/edit.php?id=' . $item['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="<?= adminUrl('news/delete.php') ?>" class="d-inline" onsubmit="return confirm('Delete this article?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($result['total'] > ADMIN_PER_PAGE): ?>
    <div class="card-footer"><?= renderPagination($result['total'], $page, ADMIN_PER_PAGE, $baseUrl) ?></div>
    <?php endif; ?>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
