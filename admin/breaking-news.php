<?php
/**
 * Admin - Breaking News CRUD
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('breaking');

$pageTitle = 'Breaking News';
$breakingModel = new BreakingNewsModel();
$newsModel = new NewsModel();
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/breaking-news.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $item = $breakingModel->findById($id);
        if ($item) {
            $breakingModel->delete($id);
            ActivityLogModel::log(Auth::id(), 'delete', 'breaking', 'Deleted breaking news: ' . $item['title'], $id);
            Session::flash('success', 'Breaking news deleted.');
        }
        redirect(ADMIN_URL . '/breaking-news.php');
    }

    $title = trim($_POST['title'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (empty($title)) {
        Session::flash('error', 'Title is required.');
        redirect(ADMIN_URL . '/breaking-news.php' . ($id ? '?edit=' . $id : ''));
    }

    $data = [
        'title'         => Security::sanitize($title),
        'news_id'       => (int) ($_POST['news_id'] ?? 0) ?: null,
        'link'          => Security::sanitize($_POST['link'] ?? ''),
        'status'        => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'expires_at'    => !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null,
    ];

    if ($id) {
        $breakingModel->update($id, $data);
        ActivityLogModel::log(Auth::id(), 'update', 'breaking', 'Updated breaking news: ' . $data['title'], $id);
        Session::flash('success', 'Breaking news updated.');
    } else {
        $newId = $breakingModel->create($data);
        ActivityLogModel::log(Auth::id(), 'create', 'breaking', 'Created breaking news: ' . $data['title'], $newId);
        Session::flash('success', 'Breaking news created.');
    }
    redirect(ADMIN_URL . '/breaking-news.php');
}

if (!empty($_GET['edit'])) {
    $editItem = $breakingModel->findById((int) $_GET['edit']);
}

$items = $breakingModel->getAll();
$publishedNews = $newsModel->getAll(['status' => 'published'], 1, 100)['data'];

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">Breaking News</h4>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3"><?= $editItem ? 'Edit Item' : 'Add Breaking News' ?></h5>
                    <form method="POST">
                        <?= Security::csrfField() ?>
                        <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= e($editItem['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link to Article</label>
                            <select name="news_id" class="form-select">
                                <option value="">Custom link below</option>
                                <?php foreach ($publishedNews as $n): ?>
                                <option value="<?= $n['id'] ?>" <?= (int)($editItem['news_id'] ?? 0) === (int)$n['id'] ? 'selected' : '' ?>><?= e(truncate($n['title'], 60)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Custom Link</label>
                            <input type="text" name="link" class="form-control" value="<?= e($editItem['link'] ?? '') ?>" placeholder="/news/slug or full URL">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="form-control" value="<?= !empty($editItem['expires_at']) ? date('Y-m-d\TH:i', strtotime($editItem['expires_at'])) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="<?= e($editItem['display_order'] ?? '0') ?>" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($editItem['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($editItem['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger"><?= $editItem ? 'Update' : 'Create' ?></button>
                            <?php if ($editItem): ?>
                            <a href="<?= ADMIN_URL ?>/breaking-news.php" class="btn btn-outline-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr><th>ID</th><th>Title</th><th>Article</th><th>Link</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= $item['id'] ?></td>
                                    <td><?= e($item['title']) ?></td>
                                    <td><?= e($item['news_title'] ?? '-') ?></td>
                                    <td><small><?= e(truncate($item['link'] ?? '-', 30)) ?></small></td>
                                    <td><?= $item['display_order'] ?></td>
                                    <td><span class="badge bg-<?= $item['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($item['status']) ?></span></td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/breaking-news.php?edit=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
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
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
