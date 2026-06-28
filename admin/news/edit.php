<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('news');

$id = (int) ($_GET['id'] ?? 0);
$newsModel = new NewsModel();
$categoryModel = new CategoryModel();
$subCategoryModel = new SubCategoryModel();

$news = $newsModel->findById($id);
if (!$news) {
    Session::flash('error', 'News article not found.');
    redirect(adminUrl('news/index.php'));
}

if (Auth::role() === 'author' && (int) $news['author_id'] !== Auth::id()) {
    Session::flash('error', 'You cannot edit this article.');
    redirect(adminUrl('news/index.php'));
}

$pageTitle = 'Edit News';
$currentPage = 'news';
$categories = $categoryModel->getAll('active');
$subCategories = $subCategoryModel->getAll(null, 'active');
$tags = $newsModel->getTags($id);
$gallery = $newsModel->getImages($id);
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
        'featured_image'   => $news['featured_image'],
        'og_image'         => $news['og_image'],
    ];

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($content)) $errors[] = 'Content is required.';
    if ($newsModel->slugExists($slug, $id)) {
        $data['slug'] = uniqueSlug(Database::getInstance(), 'news', $slug, $id);
    }

    if (!empty($_FILES['featured_image']['name'])) {
        $upload = uploadFile($_FILES['featured_image'], 'news', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            if ($news['featured_image']) deleteUploadedFile($news['featured_image']);
            $data['featured_image'] = $upload['path'];
        } else {
            $errors[] = $upload['message'];
        }
    }

    if (!empty($_FILES['og_image']['name'])) {
        $upload = uploadFile($_FILES['og_image'], 'news', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            if ($news['og_image']) deleteUploadedFile($news['og_image']);
            $data['og_image'] = $upload['path'];
        }
    }

    $data['youtube_embed'] = $data['video_url'] ? getYouTubeEmbed($data['video_url']) : null;

    if (empty($errors)) {
        $newsModel->update($id, $data);

        $tagNames = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        $newsModel->syncTags($id, $tagNames);

        $existingGallery = [];
        foreach ($gallery as $img) {
            if (!in_array($img['id'], array_map('intval', $_POST['remove_gallery'] ?? []), true)) {
                $existingGallery[] = ['path' => $img['image_path'], 'caption' => $img['caption'] ?? ''];
            } else {
                deleteUploadedFile($img['image_path']);
            }
        }

        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['name'] as $i => $name) {
                if (empty($name)) continue;
                $file = [
                    'name' => $name, 'type' => $_FILES['gallery']['type'][$i],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                    'error' => $_FILES['gallery']['error'][$i], 'size' => $_FILES['gallery']['size'][$i],
                ];
                $upload = uploadFile($file, 'news/gallery', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
                if ($upload['success']) {
                    $existingGallery[] = ['path' => $upload['path'], 'caption' => ''];
                }
            }
        }
        $newsModel->syncImages($id, $existingGallery);

        logActivity('update', 'news', $id, 'Updated: ' . $title);
        Session::flash('success', 'News article updated successfully.');
        redirect(adminUrl('news/edit.php?id=' . $id));
    }
}

$tagString = implode(', ', array_column($tags, 'name'));
$extraScripts = '<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>';
require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit News Article</h1>
    <div>
        <a href="<?= newsUrl($news['slug']) ?>" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i> Preview</a>
        <a href="<?= adminUrl('news/index.php') ?>" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
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
                        <input type="text" name="title" id="title" class="form-control" value="<?= e($_POST['title'] ?? $news['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" value="<?= e($_POST['slug'] ?? $news['slug']) ?>" data-manual="true">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="2"><?= e($_POST['excerpt'] ?? $news['excerpt'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea name="content" id="content" class="form-control" rows="12"><?= e($_POST['content'] ?? $news['content']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">SEO Settings</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?= e($_POST['meta_title'] ?? $news['meta_title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2"><?= e($_POST['meta_description'] ?? $news['meta_description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?= e($_POST['meta_keywords'] ?? $news['meta_keywords'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control" value="<?= e($_POST['canonical_url'] ?? $news['canonical_url'] ?? '') ?>">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">OG Image</label>
                        <?php if ($news['og_image']): ?><img src="<?= uploadUrl($news['og_image']) ?>" class="d-block mb-2 rounded" width="100"><?php endif; ?>
                        <input type="file" name="og_image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Publish</h6></div>
                <div class="card-body">
                    <div class="mb-2"><small class="text-muted">Views: <?= number_format((int) $news['views']) ?></small></div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" id="statusSelect">
                            <?php $curStatus = $_POST['status'] ?? $news['status']; foreach (['draft', 'published', 'scheduled', 'archived'] as $st): ?>
                            <option value="<?= $st ?>" <?= $curStatus === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="scheduleField">
                        <label class="form-label">Schedule Date</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= e($_POST['scheduled_at'] ?? ($news['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($news['scheduled_at'])) : '')) ?>">
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Update Article</button>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Category</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php $curCat = $_POST['category_id'] ?? $news['category_id']; foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (string) $curCat === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Sub Category</label>
                        <select name="sub_category_id" class="form-select">
                            <option value="">Select Sub Category</option>
                            <?php $curSub = $_POST['sub_category_id'] ?? $news['sub_category_id']; foreach ($subCategories as $sub): ?>
                            <option value="<?= $sub['id'] ?>" <?= (string) $curSub === (string) $sub['id'] ? 'selected' : '' ?>><?= e($sub['category_name'] . ' → ' . $sub['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Featured Image</h6></div>
                <div class="card-body">
                    <?php if ($news['featured_image']): ?><img src="<?= uploadUrl($news['featured_image']) ?>" class="img-fluid rounded mb-2"><?php endif; ?>
                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Gallery</h6></div>
                <div class="card-body">
                    <?php if ($gallery): ?>
                    <div class="row g-2 mb-2">
                        <?php foreach ($gallery as $img): ?>
                        <div class="col-6">
                            <img src="<?= uploadUrl($img['image_path']) ?>" class="img-fluid rounded">
                            <label class="small text-danger"><input type="checkbox" name="remove_gallery[]" value="<?= $img['id'] ?>"> Remove</label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Tags & Video</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" value="<?= e($_POST['tags'] ?? $tagString) ?>">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Video URL</label>
                        <input type="url" name="video_url" class="form-control" value="<?= e($_POST['video_url'] ?? $news['video_url'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Options</h6></div>
                <div class="card-body">
                    <?php $feat = isset($_POST['is_featured']) ? true : (bool) $news['is_featured']; ?>
                    <div class="form-check mb-2"><input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" <?= $feat ? 'checked' : '' ?>><label class="form-check-label" for="isFeatured">Featured</label></div>
                    <?php $trend = isset($_POST['is_trending']) ? true : (bool) $news['is_trending']; ?>
                    <div class="form-check mb-2"><input type="checkbox" name="is_trending" class="form-check-input" id="isTrending" <?= $trend ? 'checked' : '' ?>><label class="form-check-label" for="isTrending">Trending</label></div>
                    <?php $pick = isset($_POST['is_editors_pick']) ? true : (bool) $news['is_editors_pick']; ?>
                    <div class="form-check mb-2"><input type="checkbox" name="is_editors_pick" class="form-check-input" id="isEditorsPick" <?= $pick ? 'checked' : '' ?>><label class="form-check-label" for="isEditorsPick">Editor's Pick</label></div>
                    <div class="mb-0">
                        <label class="form-label">Featured Order</label>
                        <input type="number" name="featured_order" class="form-control" value="<?= e($_POST['featured_order'] ?? $news['featured_order']) ?>" min="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('statusSelect')?.addEventListener('change', function() {
    document.getElementById('scheduleField').style.display = this.value === 'scheduled' ? 'block' : 'none';
});
document.getElementById('statusSelect')?.dispatchEvent(new Event('change'));
</script>

<?php require APP_ROOT . '/includes/footer.php'; ?>
