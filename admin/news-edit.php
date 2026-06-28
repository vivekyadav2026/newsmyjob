<?php
/**
 * Admin - Edit News
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('news');

$id = (int) ($_GET['id'] ?? 0);
$newsModel = new NewsModel();
$categoryModel = new CategoryModel();
$subCategoryModel = new SubCategoryModel();
$mediaModel = new MediaModel();

$article = $newsModel->findById($id);
if (!$article) {
    Session::flash('error', 'Article not found.');
    redirect(ADMIN_URL . '/news.php');
}

$pageTitle = 'Edit: ' . truncate($article['title'], 40);
$categories = $categoryModel->getAll('active');
$subCategories = $subCategoryModel->getAll();
$tags = $newsModel->getTags($id);
$tagString = implode(', ', array_column($tags, 'name'));
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
        if ($newsModel->slugExists($slug, $id)) {
            $errors[] = 'Slug already exists.';
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
                'featured_image'  => $article['featured_image'],
                'category_id'     => (int) ($_POST['category_id'] ?? 0),
                'sub_category_id' => (int) ($_POST['sub_category_id'] ?? 0),
                'status'          => $status,
                'is_featured'     => isset($_POST['is_featured']) ? 1 : 0,
                'is_trending'     => isset($_POST['is_trending']) ? 1 : 0,
                'is_editors_pick' => isset($_POST['is_editors_pick']) ? 1 : 0,
                'featured_order'  => (int) ($_POST['featured_order'] ?? 0),
                'meta_title'      => Security::sanitize($_POST['meta_title'] ?? ''),
                'meta_description'=> Security::sanitize($_POST['meta_description'] ?? ''),
                'meta_keywords'   => Security::sanitize($_POST['meta_keywords'] ?? ''),
                'canonical_url'   => Security::sanitize($_POST['canonical_url'] ?? ''),
                'og_image'        => $article['og_image'],
                'video_url'       => null,
                'youtube_embed'   => null,
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
                    if ($article['featured_image']) {
                        deleteUploadedFile($article['featured_image']);
                    }
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
                    if ($article['og_image']) {
                        deleteUploadedFile($article['og_image']);
                    }
                    $data['og_image'] = $upload['path'];
                }
            }

            if (!$errors) {
                $newsModel->update($id, $data);

                $tagList = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
                $newsModel->syncTags($id, $tagList);

                ActivityLogModel::log(Auth::id(), 'update', 'news', 'Updated article: ' . $data['title'], $id);
                Session::flash('success', 'Article updated successfully.');
                redirect(ADMIN_URL . '/news-edit.php?id=' . $id);
            }
        }
    }
    $article = array_merge($article, $_POST);
    $tagString = $_POST['tags'] ?? $tagString;
} else {
    $_POST = $article;
    $_POST['tags'] = $tagString;
    $_POST['youtube_url'] = $article['video_url'] ?? '';
    if ($article['scheduled_at']) {
        $_POST['scheduled_at'] = date('Y-m-d\TH:i', strtotime($article['scheduled_at']));
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
            <h4 class="mb-0">Edit News Article</h4>
            <div>
                <?php if ($article['status'] === 'published'): ?>
                <a href="<?= newsUrl($article['slug']) ?>" target="_blank" class="btn btn-outline-secondary me-2"><i class="bi bi-eye me-1"></i> View</a>
                <?php endif; ?>
                <a href="<?= ADMIN_URL ?>/news.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
            </div>
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
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>" data-manual="true">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Excerpt</label>
                            <textarea name="excerpt" class="form-control" rows="3"><?= e($_POST['excerpt'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control"><?= e($_POST['content'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="<?= e($_POST['youtube_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
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
                            <input type="text" name="meta_keywords" class="form-control" value="<?= e($_POST['meta_keywords'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Canonical URL</label>
                            <input type="url" name="canonical_url" class="form-control" value="<?= e($_POST['canonical_url'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">OG Image</label>
                            <?php if (!empty($article['og_image'])): ?>
                            <div class="mb-2"><img src="<?= uploadUrl($article['og_image']) ?>" alt="" class="img-thumbnail" style="max-height:80px;"></div>
                            <?php endif; ?>
                            <input type="file" name="og_image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="content-card mb-4">
                        <h5 class="mb-3">Publish</h5>
                        <p class="small text-muted">Views: <?= number_format($article['views']) ?> | Read time: <?= $article['read_time'] ?> min</p>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <?php foreach (['draft', 'published', 'scheduled'] as $st): ?>
                                <option value="<?= $st ?>" <?= ($_POST['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3" id="scheduledWrap">
                            <label class="form-label">Schedule Date & Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= e($_POST['scheduled_at'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-danger w-100"><i class="bi bi-check-lg me-1"></i> Update Article</button>
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
                        <?php if (!empty($article['featured_image'])): ?>
                        <div class="mb-2"><img src="<?= uploadUrl($article['featured_image']) ?>" alt="" class="img-fluid rounded mb-2"></div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" class="form-control" accept="image/*">
                    </div>

                    <div class="content-card mb-4">
                        <h5 class="mb-3">Tags</h5>
                        <input type="text" name="tags" class="form-control" value="<?= e($tagString) ?>">
                    </div>

                    <div class="content-card">
                        <h5 class="mb-3">Options</h5>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" <?= !empty($_POST['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_trending" class="form-check-input" id="is_trending" <?= !empty($_POST['is_trending']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_trending">Trending</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_editors_pick" class="form-check-input" id="is_editors_pick" <?= !empty($_POST['is_editors_pick']) ? 'checked' : '' ?>>
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
$selectedSub = (int)($_POST['sub_category_id'] ?? 0);
$extraScripts = <<<JS
<script>
const allSubCategories = {$subCatJson};
const selectedSub = {$selectedSub};
function filterSubCategories() {
    const catId = parseInt(document.getElementById('category_id').value) || 0;
    const select = document.getElementById('sub_category_id');
    select.innerHTML = '<option value="">Select Sub Category</option>';
    allSubCategories.filter(sc => !catId || sc.category_id == catId).forEach(sc => {
        const opt = document.createElement('option');
        opt.value = sc.id;
        opt.textContent = sc.name;
        if (sc.id == selectedSub) opt.selected = true;
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
