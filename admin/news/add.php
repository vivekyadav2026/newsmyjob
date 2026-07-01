<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('news');

$pageTitle = 'Add News';
$currentPage = 'news';

$newsModel = new NewsModel();
$categoryModel = new CategoryModel();
$subCategoryModel = new SubCategoryModel();
$categories = $categoryModel->getAll('active');
$subCategories = $subCategoryModel->getAll(null, 'active');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: generateSlug($title);
    $content = cleanHtml($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    $data = [
        'title'            => $title,
        'slug'             => $slug,
        'excerpt'          => trim($_POST['excerpt'] ?? ''),
        'content'          => $content,
        'category_id'      => (int) ($_POST['category_id'] ?? 0),
        'sub_category_id'  => (int) ($_POST['sub_category_id'] ?? 0),
        'author_id'        => Auth::id(),
        'status'           => $status,
        'is_featured'      => isset($_POST['is_featured']) ? 1 : 0,
        'is_trending'      => isset($_POST['is_trending']) ? 1 : 0,
        'is_editors_pick'  => isset($_POST['is_editors_pick']) ? 1 : 0,
        'featured_order'   => (int) ($_POST['featured_order'] ?? 0),
        'meta_title'       => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'meta_keywords'    => trim($_POST['meta_keywords'] ?? ''),
        'canonical_url'    => trim($_POST['canonical_url'] ?? ''),
        'video_url'        => trim($_POST['video_url'] ?? ''),
        'scheduled_at'     => !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null,
    ];

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    if ($newsModel->slugExists($slug)) {
        $slug = uniqueSlug(Database::getInstance(), 'news', $slug);
        $data['slug'] = $slug;
    }

    if (!empty($_FILES['featured_image']['name'])) {
        $upload = uploadFile($_FILES['featured_image'], 'news', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            $data['featured_image'] = $upload['path'];
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

    if ($data['video_url']) {
        $data['youtube_embed'] = getYouTubeEmbed($data['video_url']);
    }

    if (empty($errors)) {
        $newsId = $newsModel->create($data);

        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        if ($tags) {
            $newsModel->syncTags($newsId, $tags);
        }

        $galleryImages = [];
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['name'] as $i => $name) {
                if (empty($name)) continue;
                $file = [
                    'name'     => $name,
                    'type'     => $_FILES['gallery']['type'][$i],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                    'error'    => $_FILES['gallery']['error'][$i],
                    'size'     => $_FILES['gallery']['size'][$i],
                ];
                $upload = uploadFile($file, 'news/gallery', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
                if ($upload['success']) {
                    $galleryImages[] = ['path' => $upload['path'], 'caption' => ''];
                }
            }
        }
        if ($galleryImages) {
            $newsModel->syncImages($newsId, $galleryImages);
        }

        logActivity('create', 'news', $newsId, 'Created: ' . $title);
        Session::flash('success', 'News article created successfully.');
        redirect(adminUrl('news/edit.php?id=' . $newsId));
    }
}

$extraScripts = '
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    tinymce.init({
        selector: "#content",
        height: 400,
        plugins: "code link image lists table",
        toolbar: "undo redo | blocks fontfamily fontsize | bold italic underline | align lineheight | numlist bullist | link image table | code"
    });
});
</script>
';
require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add News Article</h1>
    <a href="<?= adminUrl('news/index.php') ?>" class="btn btn-outline-secondary">Back to List</a>
</div>

<?= renderFlash() ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="title" class="form-control" value="<?= e($_POST['title'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="2"><?= e($_POST['excerpt'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea name="content" id="content" class="form-control" rows="12"><?= e($_POST['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">SEO Settings</h6></div>
                <div class="card-body">
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
                        <input type="text" name="meta_keywords" class="form-control" value="<?= e($_POST['meta_keywords'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control" value="<?= e($_POST['canonical_url'] ?? '') ?>">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">OG Image</label>
                        <input type="file" name="og_image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Publish</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" id="statusSelect">
                            <?php foreach (['draft', 'published', 'scheduled', 'archived'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($_POST['status'] ?? 'draft') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="scheduleField">
                        <label class="form-label">Schedule Date</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= e($_POST['scheduled_at'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Save Article</button>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Category</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" id="categorySelect">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (string) ($_POST['category_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Sub Category</label>
                        <select name="sub_category_id" class="form-select" id="subCategorySelect">
                            <option value="">Select Sub Category</option>
                            <?php foreach ($subCategories as $sub): ?>
                            <option value="<?= $sub['id'] ?>" data-category="<?= $sub['category_id'] ?>" <?= (string) ($_POST['sub_category_id'] ?? '') === (string) $sub['id'] ? 'selected' : '' ?>><?= e($sub['category_name'] . ' → ' . $sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Featured Image</h6></div>
                <div class="card-body">
                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Gallery Images</h6></div>
                <div class="card-body">
                    <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Tags & Video</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tags (comma separated)</label>
                        <input type="text" name="tags" class="form-control" value="<?= e($_POST['tags'] ?? '') ?>" placeholder="politics, election, india">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Video URL</label>
                        <input type="url" name="video_url" class="form-control" value="<?= e($_POST['video_url'] ?? '') ?>" placeholder="YouTube URL">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Options</h6></div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isFeatured">Featured News</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_trending" class="form-check-input" id="isTrending" <?= isset($_POST['is_trending']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isTrending">Trending</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_editors_pick" class="form-check-input" id="isEditorsPick" <?= isset($_POST['is_editors_pick']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isEditorsPick">Editor's Pick</label>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Featured Order</label>
                        <input type="number" name="featured_order" class="form-control" value="<?= e($_POST['featured_order'] ?? '0') ?>" min="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('categorySelect')?.addEventListener('change', function() {
    const catId = this.value;
    document.querySelectorAll('#subCategorySelect option').forEach(opt => {
        if (!opt.value) return;
        opt.hidden = opt.dataset.category !== catId;
    });
});
document.getElementById('statusSelect')?.addEventListener('change', function() {
    document.getElementById('scheduleField').style.display = this.value === 'scheduled' ? 'block' : 'none';
});
document.getElementById('statusSelect')?.dispatchEvent(new Event('change'));
</script>

<?php require APP_ROOT . '/includes/footer.php'; ?>
