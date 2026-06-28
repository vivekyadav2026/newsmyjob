<?php
/**
 * Admin - Media Library
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('media');

$pageTitle = 'Media Library';
$mediaModel = new MediaModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/media.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $item = $mediaModel->findById($id);
        if ($item) {
            $mediaModel->delete($id);
            ActivityLogModel::log(Auth::id(), 'delete', 'media', 'Deleted media: ' . $item['original_name'], $id);
            Session::flash('success', 'Media deleted.');
        }
        redirect(ADMIN_URL . '/media.php?' . http_build_query(array_filter(['search' => $_GET['search'] ?? ''])));
    }

    if ($action === 'upload' && !empty($_FILES['files'])) {
        $uploaded = 0;
        $files = $_FILES['files'];
        $count = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'     => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                'type'     => is_array($files['type']) ? $files['type'][$i] : $files['type'],
                'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                'error'    => is_array($files['error']) ? $files['error'][$i] : $files['error'],
                'size'     => is_array($files['size']) ? $files['size'][$i] : $files['size'],
            ];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            $allowed = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES);
            $maxSize = in_array($file['type'], ALLOWED_IMAGE_TYPES, true) ? MAX_IMAGE_SIZE : MAX_DOC_SIZE;
            $upload = uploadFile($file, 'media', $allowed, $maxSize);

            if ($upload['success']) {
                $fileType = 'document';
                if (str_starts_with($upload['mime'], 'image/')) {
                    $fileType = 'image';
                } elseif ($upload['mime'] === 'application/pdf') {
                    $fileType = 'pdf';
                }

                $mediaModel->create([
                    'filename'      => $upload['filename'],
                    'original_name' => $file['name'],
                    'file_path'     => $upload['path'],
                    'file_type'     => $fileType,
                    'mime_type'     => $upload['mime'],
                    'file_size'     => $upload['size'],
                    'alt_text'      => pathinfo($file['name'], PATHINFO_FILENAME),
                    'uploaded_by'   => Auth::id(),
                ]);
                $uploaded++;
            }
        }

        if ($uploaded > 0) {
            ActivityLogModel::log(Auth::id(), 'create', 'media', "Uploaded {$uploaded} file(s)");
            Session::flash('success', "{$uploaded} file(s) uploaded.");
        } else {
            Session::flash('error', 'No files were uploaded.');
        }
        redirect(ADMIN_URL . '/media.php');
    }
}

$search = Security::sanitize($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $mediaModel->getAll(['search' => $search], $page, 24);
$mediaItems = $result['data'];
$total = $result['total'];
$queryBase = ADMIN_URL . '/media.php?' . http_build_query(array_filter(['search' => $search]));

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Media Library</h4>
        </div>

        <div class="content-card mb-4">
            <form method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="upload">
                <div class="col-md-8">
                    <label class="form-label">Upload Files</label>
                    <input type="file" name="files[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-cloud-upload me-1"></i> Upload</button>
                </div>
            </form>
        </div>

        <div class="content-card mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Search by filename...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>

        <div class="row g-3">
            <?php if (!$mediaItems): ?>
            <div class="col-12"><p class="text-muted text-center py-5">No media files found.</p></div>
            <?php endif; ?>
            <?php foreach ($mediaItems as $item): ?>
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card h-100 media-card">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:120px;overflow:hidden;">
                        <?php if ($item['file_type'] === 'image'): ?>
                        <img src="<?= uploadUrl($item['file_path']) ?>" alt="<?= e($item['alt_text'] ?? '') ?>" class="img-fluid" style="max-height:120px;object-fit:cover;width:100%;">
                        <?php elseif ($item['file_type'] === 'pdf'): ?>
                        <i class="bi bi-file-pdf text-danger" style="font-size:48px;"></i>
                        <?php else: ?>
                        <i class="bi bi-file-earmark text-secondary" style="font-size:48px;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-2">
                        <p class="small text-truncate mb-1" title="<?= e($item['original_name']) ?>"><?= e($item['original_name']) ?></p>
                        <p class="small text-muted mb-2"><?= formatFileSize((int)($item['file_size'] ?? 0)) ?></p>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill copy-url" data-url="<?= uploadUrl($item['file_path']) ?>" title="Copy URL"><i class="bi bi-clipboard"></i></button>
                            <form method="POST" class="flex-fill">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100 btn-delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <?= renderPagination($total, $page, 24, $queryBase) ?>
        </div>
    </div>
</div>
<?php
$extraScripts = <<<'JS'
<script>
document.querySelectorAll('.copy-url').forEach(btn => {
    btn.addEventListener('click', function() {
        navigator.clipboard.writeText(this.dataset.url).then(() => alert('URL copied!'));
    });
});
</script>
JS;
require VIEWS_PATH . '/admin/includes/footer.php';
?>
