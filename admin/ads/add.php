<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('ads');

$pageTitle = 'Add Advertisement';
$currentPage = 'ads';
$model = new AdvertisementModel();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'ad_type' => $_POST['ad_type'] ?? 'banner',
        'position' => trim($_POST['position'] ?? 'header'),
        'link' => trim($_POST['link'] ?? '') ?: null,
        'ad_code' => $_POST['ad_code'] ?? null,
        'width' => (int) ($_POST['width'] ?? 0) ?: null,
        'height' => (int) ($_POST['height'] ?? 0) ?: null,
        'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        'status' => $_POST['status'] ?? 'active',
        'display_order' => (int) ($_POST['display_order'] ?? 0),
    ];
    if (empty($data['title'])) $errors[] = 'Title is required.';
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadFile($_FILES['image'], 'ads', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) $data['image'] = $upload['path'];
        else $errors[] = $upload['message'];
    }
    if (empty($errors)) {
        $id = $model->create($data);
        logActivity('create', 'ads', $id, 'Created ad: ' . $data['title']);
        Session::flash('success', 'Advertisement created.');
        redirect(adminUrl('ads/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Advertisement</h1>
    <a href="<?= adminUrl('ads/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>

<div class="card shadow-sm"><div class="card-body">
<form method="POST" enctype="multipart/form-data"><?= csrfField() ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Type</label><select name="ad_type" class="form-select"><?php foreach (['banner','sidebar','footer','popup','sticky','in_article','adsense'] as $t): ?><option value="<?= $t ?>"><?= ucfirst(str_replace('_',' ',$t)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Position</label><input type="text" name="position" class="form-control" value="header"></div>
    <div class="col-md-6"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
    <div class="col-md-6"><label class="form-label">Link URL</label><input type="url" name="link" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Width</label><input type="number" name="width" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Height</label><input type="number" name="height" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    <div class="col-12"><label class="form-label">Ad Code (HTML/AdSense)</label><textarea name="ad_code" class="form-control font-monospace" rows="4"></textarea></div>
</div>
<button type="submit" class="btn btn-danger mt-3">Save Ad</button>
</form></div></div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
