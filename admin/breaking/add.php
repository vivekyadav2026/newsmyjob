<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('breaking');

$pageTitle = 'Add Breaking News';
$currentPage = 'breaking';
$model = new BreakingNewsModel();
$newsModel = new NewsModel();
$newsList = $newsModel->getAll(['status' => 'published'], 1, 100)['data'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title = trim($_POST['title'] ?? '');
    $data = [
        'title' => $title,
        'news_id' => (int) ($_POST['news_id'] ?? 0) ?: null,
        'link' => trim($_POST['link'] ?? '') ?: null,
        'status' => $_POST['status'] ?? 'active',
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
    ];
    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($errors)) {
        $id = $model->create($data);
        logActivity('create', 'breaking', $id, 'Created breaking news');
        Session::flash('success', 'Breaking news added.');
        redirect(adminUrl('breaking/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Breaking News</h1>
    <a href="<?= adminUrl('breaking/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>

<div class="card shadow-sm"><div class="card-body">
<form method="POST"><?= csrfField() ?>
<div class="row g-3">
    <div class="col-12"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Link to News Article</label><select name="news_id" class="form-select"><option value="">None</option><?php foreach ($newsList as $n): ?><option value="<?= $n['id'] ?>"><?= e(truncate($n['title'], 60)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Custom Link</label><input type="url" name="link" class="form-control" placeholder="https://"></div>
    <div class="col-md-4"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
    <div class="col-md-4"><label class="form-label">Expires At</label><input type="datetime-local" name="expires_at" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
</div>
<button type="submit" class="btn btn-danger mt-3">Save</button>
</form></div></div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
