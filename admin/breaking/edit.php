<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('breaking');

$id = (int) ($_GET['id'] ?? 0);
$model = new BreakingNewsModel();
$item = $model->findById($id);
if (!$item) { Session::flash('error', 'Not found.'); redirect(adminUrl('breaking/index.php')); }

$pageTitle = 'Edit Breaking News';
$currentPage = 'breaking';
$newsList = (new NewsModel())->getAll(['status' => 'published'], 1, 100)['data'];
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
        $model->update($id, $data);
        logActivity('update', 'breaking', $id, 'Updated breaking news');
        Session::flash('success', 'Breaking news updated.');
        redirect(adminUrl('breaking/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Breaking News</h1>
    <a href="<?= adminUrl('breaking/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?= renderFlash() ?>
<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>

<div class="card shadow-sm"><div class="card-body">
<form method="POST"><?= csrfField() ?>
<div class="row g-3">
    <div class="col-12"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="<?= e($item['title']) ?>" required></div>
    <div class="col-md-6"><label class="form-label">Link to News</label><select name="news_id" class="form-select"><option value="">None</option><?php foreach ($newsList as $n): ?><option value="<?= $n['id'] ?>" <?= (int)($item['news_id']??0) === (int)$n['id'] ? 'selected' : '' ?>><?= e(truncate($n['title'], 60)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Custom Link</label><input type="url" name="link" class="form-control" value="<?= e($item['link'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Order</label><input type="number" name="display_order" class="form-control" value="<?= (int)$item['display_order'] ?>"></div>
    <div class="col-md-4"><label class="form-label">Expires</label><input type="datetime-local" name="expires_at" class="form-control" value="<?= $item['expires_at'] ? date('Y-m-d\TH:i', strtotime($item['expires_at'])) : '' ?>"></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= $item['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $item['status']==='inactive'?'selected':'' ?>>Inactive</option></select></div>
</div>
<button type="submit" class="btn btn-danger mt-3">Update</button>
</form></div></div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
