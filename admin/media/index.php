<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('media');

$pageTitle = 'Media Library';
$currentPage = 'media';
$mediaModel = new MediaModel();
$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = [
    'file_type' => $_GET['file_type'] ?? '',
    'search'    => trim($_GET['search'] ?? ''),
];
$result = $mediaModel->getAll(array_filter($filters), $page, 24);
$baseUrl = adminUrl('media/index.php') . '?' . http_build_query(array_filter($filters));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? 'upload';

    if ($action === 'upload' && !empty($_FILES['files']['name'][0])) {
        $uploaded = 0;
        foreach ($_FILES['files']['name'] as $i => $name) {
            if (empty($name)) continue;
            $file = [
                'name' => $name, 'type' => $_FILES['files']['type'][$i],
                'tmp_name' => $_FILES['files']['tmp_name'][$i],
                'error' => $_FILES['files']['error'][$i], 'size' => $_FILES['files']['size'][$i],
            ];
            $mime = $file['type'];
            $isImage = str_starts_with($mime, 'image/');
            $allowed = $isImage ? ALLOWED_IMAGE_TYPES : ALLOWED_DOC_TYPES;
            $maxSize = $isImage ? MAX_IMAGE_SIZE : MAX_DOC_SIZE;
            $upload = uploadFile($file, 'media', $allowed, $maxSize);
            if ($upload['success']) {
                $mediaModel->create([
                    'filename' => $upload['filename'],
                    'original_name' => $name,
                    'file_path' => $upload['path'],
                    'file_type' => $isImage ? 'image' : 'document',
                    'mime_type' => $upload['mime'],
                    'file_size' => $upload['size'],
                    'uploaded_by' => Auth::id(),
                ]);
                $uploaded++;
            }
        }
        logActivity('upload', 'media', null, "Uploaded {$uploaded} file(s)");
        Session::flash('success', "{$uploaded} file(s) uploaded.");
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($mediaModel->delete($id)) {
            logActivity('delete', 'media', $id, 'Deleted media file');
            Session::flash('success', 'Media deleted.');
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $mediaModel->update($id, [
            'alt_text' => trim($_POST['alt_text'] ?? ''),
            'caption'  => trim($_POST['caption'] ?? ''),
        ]);
        Session::flash('success', 'Media updated.');
    }
    redirect(adminUrl('media/index.php'));
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Media Library</h1>
</div>

<?= renderFlash() ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="upload">
            <div class="col-md-8">
                <label class="form-label">Upload Files</label>
                <input type="file" name="files[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-danger w-100"><i class="bi bi-upload"></i> Upload</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search..." value="<?= e($filters['search']) ?>"></div>
            <div class="col-md-3">
                <select name="file_type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach (['image', 'document', 'video', 'audio'] as $t): ?>
                    <option value="<?= $t ?>" <?= $filters['file_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($result['data'])): ?>
    <div class="col-12 text-center text-muted py-5">No media files found</div>
    <?php else: foreach ($result['data'] as $media): ?>
    <div class="col-md-3 col-sm-4 col-6">
        <div class="card shadow-sm h-100">
            <?php if ($media['file_type'] === 'image'): ?>
            <img src="<?= uploadUrl($media['file_path']) ?>" class="card-img-top" style="height:140px;object-fit:cover;" alt="<?= e($media['alt_text'] ?? '') ?>">
            <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:140px;"><i class="bi bi-file-earmark fs-1 text-muted"></i></div>
            <?php endif; ?>
            <div class="card-body p-2">
                <div class="small fw-semibold text-truncate" title="<?= e($media['original_name']) ?>"><?= e($media['original_name']) ?></div>
                <div class="text-muted small"><?= formatFileSize((int) $media['file_size']) ?></div>
                <input type="text" class="form-control form-control-sm mt-1" value="<?= e(uploadUrl($media['file_path'])) ?>" readonly onclick="this.select()">
                <div class="d-flex gap-1 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#editModal<?= $media['id'] ?>">Edit</button>
                    <form method="POST" class="flex-fill" onsubmit="return confirm('Delete?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $media['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editModal<?= $media['id'] ?>" tabindex="-1">
            <div class="modal-dialog"><div class="modal-content">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $media['id'] ?>">
                    <div class="modal-header"><h5 class="modal-title">Edit Media</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Alt Text</label><input type="text" name="alt_text" class="form-control" value="<?= e($media['alt_text'] ?? '') ?>"></div>
                        <div class="mb-0"><label class="form-label">Caption</label><textarea name="caption" class="form-control" rows="2"><?= e($media['caption'] ?? '') ?></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-danger">Save</button></div>
                </form>
            </div></div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<?php if ($result['total'] > 24): ?>
<div class="mt-3"><?= renderPagination($result['total'], $page, 24, $baseUrl) ?></div>
<?php endif; ?>

<?php require APP_ROOT . '/includes/footer.php'; ?>
