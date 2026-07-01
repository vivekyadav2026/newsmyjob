<?php
/**
 * Admin - Add News
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('news');

$pageTitle = 'Add News';
$newsModel = new NewsModel();
$categoryModel = new CategoryModel();
$subCategoryModel = new SubCategoryModel();
$mediaModel = new MediaModel();

$categories = $categoryModel->getAll('active');
$subCategories = $subCategoryModel->getAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        $errors[] = 'Invalid security token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = generateSlug($_POST['slug'] ?? $title);
        $content = $_POST['content'] ?? '';

        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        if (empty($content)) {
            $errors[] = 'Content is required.';
        }
        if ($newsModel->slugExists($slug)) {
            $slug = $slug . '-' . time();
        }

        if (!$errors) {
            $status = $_POST['status'] ?? 'draft';
            if (!in_array($status, ['draft', 'published', 'scheduled'], true)) {
                $status = 'draft';
            }

            $data = [
                'title'           => Security::sanitize($title),
                'slug'            => $slug,
                'excerpt'         => Security::sanitize($_POST['excerpt'] ?? ''),
                'content'         => Security::sanitizeHtml($content),
                'category_id'     => (int) ($_POST['category_id'] ?? 0),
                'sub_category_id' => (int) ($_POST['sub_category_id'] ?? 0),
                'author_id'       => Auth::id(),
                'status'          => $status,
                'is_featured'     => isset($_POST['is_featured']) ? 1 : 0,
                'is_trending'     => isset($_POST['is_trending']) ? 1 : 0,
                'is_editors_pick' => isset($_POST['is_editors_pick']) ? 1 : 0,
                'featured_order'  => (int) ($_POST['featured_order'] ?? 0),
                'meta_title'      => Security::sanitize($_POST['meta_title'] ?? ''),
                'meta_description'=> Security::sanitize($_POST['meta_description'] ?? ''),
                'meta_keywords'   => Security::sanitize($_POST['meta_keywords'] ?? ''),
                'canonical_url'   => Security::sanitize($_POST['canonical_url'] ?? ''),
                'scheduled_at'    => null,
            ];

            if ($status === 'scheduled' && !empty($_POST['scheduled_at'])) {
                $data['scheduled_at'] = date('Y-m-d H:i:s', strtotime($_POST['scheduled_at']));
            }

            $youtubeUrl = trim($_POST['youtube_url'] ?? '');
            if ($youtubeUrl) {
                $data['video_url'] = $youtubeUrl;
                $data['youtube_embed'] = getYouTubeEmbed($youtubeUrl);
            }

            if (!empty($_FILES['featured_image']['name'])) {
                $upload = uploadFile($_FILES['featured_image'], 'news', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
                if ($upload['success']) {
                    $data['featured_image'] = $upload['path'];
                    $mediaModel->create([
                        'filename'      => $upload['filename'],
                        'original_name' => $_FILES['featured_image']['name'],
                        'file_path'     => $upload['path'],
                        'file_type'     => 'image',
                        'mime_type'     => $upload['mime'],
                        'file_size'     => $upload['size'],
                        'uploaded_by'   => Auth::id(),
                    ]);
                } else {
                    $errors[] = $upload['message'];
                }
            }

            if (!empty($_FILES['og_image']['name'])) {
                $upload = uploadFile($_FILES['og_image'], 'news', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
                if ($upload['success']) {
                    $data['og_image'] = $upload['path'];
                }
            }

            if (!$errors) {
                $newsId = $newsModel->create($data);

                $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
                if ($tags) {
                    $newsModel->syncTags($newsId, $tags);
                }

                ActivityLogModel::log(Auth::id(), 'create', 'news', 'Created article: ' . $data['title'], $newsId);
                Session::flash('success', 'Article created successfully.');
                redirect(ADMIN_URL . '/news-edit.php?id=' . $newsId);
            }
        }
    }
}

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
$subCatJson = json_encode(array_map(fn($sc) => ['id' => $sc['id'], 'category_id' => $sc['category_id'], 'name' => $sc['name']], $subCategories));
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Add News Article</h4>
            <a href="<?= ADMIN_URL ?>/news.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
        </div>

        <?php if ($errors): ?>
        <div class="alert alert-danger"><?= e($errors[0]) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <?= Security::csrfField() ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="content-card mb-4">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="<?= e($_POST['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>" placeholder="Auto-generated from title">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Excerpt</label>
                            <textarea name="excerpt" class="form-control" rows="3"><?= e($_POST['excerpt'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control" rows="20"><?= e($_POST['content'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="<?= e($_POST['youtube_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
                            <small class="text-muted">Optional video embed for this article</small>
                        </div>
                    </div>

                    <div class="content-card">
                        <h5 class="mb-3">SEO Settings</h5>
                        <div class="mb-3">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" value="<?= e($_POST['meta_title'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2"><?= e($_POST['meta_description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control" value="<?= e($_POST['meta_keywords'] ?? '') ?>" placeholder="keyword1, keyword2">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Canonical URL</label>
                            <input type="url" name="canonical_url" class="form-control" value="<?= e($_POST['canonical_url'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">OG Image</label>
                            <input type="file" name="og_image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="content-card mb-4">
                        <h5 class="mb-3">Publish</h5>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <?php foreach (['draft', 'published', 'scheduled'] as $st): ?>
                                <option value="<?= $st ?>" <?= ($_POST['status'] ?? 'draft') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3" id="scheduledWrap" style="display:none;">
                            <label class="form-label">Schedule Date & Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= e($_POST['scheduled_at'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-danger w-100"><i class="bi bi-check-lg me-1"></i> Save Article</button>
                    </div>

                    <div class="content-card mb-4">
                        <h5 class="mb-3">Category</h5>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (int)($_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sub Category</label>
                            <select name="sub_category_id" id="sub_category_id" class="form-select">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                    </div>

                    <div class="content-card mb-4">
                        <h5 class="mb-3">Featured Image</h5>
                        <input type="file" name="featured_image" class="form-control" accept="image/*">
                    </div>

                    <div class="content-card mb-4">
                        <h5 class="mb-3">Tags</h5>
                        <input type="text" name="tags" class="form-control" value="<?= e($_POST['tags'] ?? '') ?>" placeholder="politics, economy, india">
                        <small class="text-muted">Comma-separated tags</small>
                    </div>

                    <div class="content-card">
                        <h5 class="mb-3">Options</h5>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_trending" class="form-check-input" id="is_trending" <?= isset($_POST['is_trending']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_trending">Trending</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_editors_pick" class="form-check-input" id="is_editors_pick" <?= isset($_POST['is_editors_pick']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_editors_pick">Editor's Pick</label>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Featured Order</label>
                            <input type="number" name="featured_order" class="form-control" value="<?= e($_POST['featured_order'] ?? '0') ?>" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$extraScripts = <<<JS
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: 'code link image lists table',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | align lineheight | numlist bullist | link image table | code'
        });
    } else {
        alert("Editor failed to load. Please check your internet connection or adblocker.");
    }
const allSubCategories = {$subCatJson};
function filterSubCategories() {
    const catId = parseInt(document.getElementById('category_id').value) || 0;
    const select = document.getElementById('sub_category_id');
    const current = select.value;
    select.innerHTML = '<option value="">Select Sub Category</option>';
    allSubCategories.filter(sc => !catId || sc.category_id == catId).forEach(sc => {
        const opt = document.createElement('option');
        opt.value = sc.id;
        opt.textContent = sc.name;
        if (sc.id == current) opt.selected = true;
        select.appendChild(opt);
    });
}
document.getElementById('category_id').addEventListener('change', filterSubCategories);
filterSubCategories();
document.getElementById('status').addEventListener('change', function() {
    document.getElementById('scheduledWrap').style.display = this.value === 'scheduled' ? 'block' : 'none';
});
document.getElementById('status').dispatchEvent(new Event('change'));
</script>
JS;
require VIEWS_PATH . '/admin/includes/footer.php';
?>
