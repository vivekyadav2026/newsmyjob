<?php
/**
 * Admin - Advertisements CRUD
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('ads');

$pageTitle = 'Advertisements';
$adModel = new AdvertisementModel();
$editItem = null;
$adTypes = ['image', 'html', 'adsense'];
$positions = ['header', 'sidebar', 'footer', 'popup', 'sticky', 'content'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/advertisements.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $item = $adModel->findById($id);
        if ($item) {
            if ($item['image']) {
                deleteUploadedFile($item['image']);
            }
            $adModel->delete($id);
            ActivityLogModel::log(Auth::id(), 'delete', 'ads', 'Deleted ad: ' . $item['title'], $id);
            Session::flash('success', 'Advertisement deleted.');
        }
        redirect(ADMIN_URL . '/advertisements.php');
    }

    $title = trim($_POST['title'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if (empty($title)) {
        Session::flash('error', 'Title is required.');
        redirect(ADMIN_URL . '/advertisements.php' . ($id ? '?edit=' . $id : ''));
    }

    $data = [
        'title'         => Security::sanitize($title),
        'ad_type'       => in_array($_POST['ad_type'] ?? '', $adTypes, true) ? $_POST['ad_type'] : 'html',
        'position'      => in_array($_POST['position'] ?? '', $positions, true) ? $_POST['position'] : 'sidebar',
        'link'          => Security::sanitize($_POST['link'] ?? ''),
        'content'       => $_POST['content'] ?? '',
        'start_date'    => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        'end_date'      => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        'status'        => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'image'         => null,
    ];

    if ($id) {
        $existing = $adModel->findById($id);
        $data['image'] = $existing['image'] ?? null;
    }

    if (!empty($_FILES['image']['name'])) {
        $upload = uploadFile($_FILES['image'], 'ads', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            if ($id && !empty($data['image'])) {
                deleteUploadedFile($data['image']);
            }
            $data['image'] = $upload['path'];
        }
    }

    if ($id) {
        $adModel->update($id, $data);
        ActivityLogModel::log(Auth::id(), 'update', 'ads', 'Updated ad: ' . $data['title'], $id);
        Session::flash('success', 'Advertisement updated.');
    } else {
        $newId = $adModel->create($data);
        ActivityLogModel::log(Auth::id(), 'create', 'ads', 'Created ad: ' . $data['title'], $newId);
        Session::flash('success', 'Advertisement created.');
    }
    redirect(ADMIN_URL . '/advertisements.php');
}

if (!empty($_GET['edit'])) {
    $editItem = $adModel->findById((int) $_GET['edit']);
}

$ads = $adModel->getAll();

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">Advertisements</h4>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3"><?= $editItem ? 'Edit Ad' : 'Add Advertisement' ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <?= Security::csrfField() ?>
                        <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= e($editItem['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ad Type</label>
                            <select name="ad_type" id="ad_type" class="form-select">
                                <?php foreach ($adTypes as $t): ?>
                                <option value="<?= $t ?>" <?= ($editItem['ad_type'] ?? 'html') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <select name="position" class="form-select">
                                <?php foreach ($positions as $p): ?>
                                <option value="<?= $p ?>" <?= ($editItem['position'] ?? 'sidebar') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3 ad-image-field">
                            <label class="form-label">Ad Image</label>
                            <?php if (!empty($editItem['image'])): ?>
                            <div class="mb-2"><img src="<?= uploadUrl($editItem['image']) ?>" alt="" class="img-thumbnail" style="max-height:80px;"></div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3 ad-image-field">
                            <label class="form-label">Click Link</label>
                            <input type="url" name="link" class="form-control" value="<?= e($editItem['link'] ?? '') ?>">
                        </div>
                        <div class="mb-3 ad-code-field">
                            <label class="form-label">Ad Code / HTML</label>
                            <textarea name="content" class="form-control" rows="4"><?= e($editItem['content'] ?? '') ?></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?= e($editItem['start_date'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?= e($editItem['end_date'] ?? '') ?>">
                            </div>
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
                            <a href="<?= ADMIN_URL ?>/advertisements.php" class="btn btn-outline-secondary">Cancel</a>
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
                                <tr><th>ID</th><th>Title</th><th>Type</th><th>Position</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ads as $ad): ?>
                                <tr>
                                    <td><?= $ad['id'] ?></td>
                                    <td><?= e($ad['title']) ?></td>
                                    <td><span class="badge bg-info"><?= ucfirst($ad['ad_type']) ?></span></td>
                                    <td><?= ucfirst($ad['position']) ?></td>
                                    <td><span class="badge bg-<?= $ad['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($ad['status']) ?></span></td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/advertisements.php?edit=<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $ad['id'] ?>">
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
<?php
$extraScripts = <<<'JS'
<script>
function toggleAdFields() {
    const type = document.getElementById('ad_type').value;
    document.querySelectorAll('.ad-image-field').forEach(el => {
        el.style.display = type === 'image' ? 'block' : 'none';
    });
    document.querySelectorAll('.ad-code-field').forEach(el => {
        el.style.display = (type === 'html' || type === 'adsense') ? 'block' : 'none';
    });
}
document.getElementById('ad_type').addEventListener('change', toggleAdFields);
toggleAdFields();
</script>
JS;
require VIEWS_PATH . '/admin/includes/footer.php';
?>
