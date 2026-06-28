<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('seo');

$pageTitle = 'SEO Settings';
$currentPage = 'seo';
$settingsModel = new SettingsModel();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        foreach (['meta_title', 'meta_description', 'meta_keywords', 'google_analytics', 'google_adsense'] as $key) {
            if (isset($_POST[$key])) {
                $group = in_array($key, ['google_analytics', 'google_adsense'], true) ? 'seo' : 'seo';
                $settingsModel->set($key, trim($_POST[$key]), $group);
            }
        }
        if (isset($_POST['robots_txt'])) {
            file_put_contents(APP_ROOT . '/robots.txt', $_POST['robots_txt']);
        }
        logActivity('update', 'seo', null, 'Updated SEO settings');
        Session::flash('success', 'SEO settings saved.');
        redirect(adminUrl('seo/index.php'));
    }

    if ($action === 'sitemap') {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $published = $newsModel->getAll(['status' => 'published'], 1, 5000);
        $categories = $categoryModel->getAll('active');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '<url><loc>' . e(BASE_URL) . '/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n";

        foreach ($categories as $cat) {
            $xml .= '<url><loc>' . e(BASE_URL . '/category/' . $cat['slug']) . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . "\n";
        }
        foreach ($published['data'] as $item) {
            $lastmod = $item['updated_at'] ? date('Y-m-d', strtotime($item['updated_at'])) : date('Y-m-d');
            $xml .= '<url><loc>' . e(BASE_URL . '/news/' . $item['slug']) . '</loc><lastmod>' . $lastmod . '</lastmod><changefreq>weekly</changefreq><priority>0.7</priority></url>' . "\n";
        }
        $xml .= '</urlset>';

        $sitemapPath = APP_ROOT . '/sitemap.xml';
        if (file_put_contents($sitemapPath, $xml) !== false) {
            logActivity('generate', 'seo', null, 'Generated sitemap.xml');
            $message = 'Sitemap generated successfully with ' . count($published['data']) . ' news URLs.';
        } else {
            Session::flash('error', 'Failed to write sitemap.xml. Check file permissions.');
            redirect(adminUrl('seo/index.php'));
        }
    }
}

$settings = $settingsModel->getAllAsArray();
$robotsPath = APP_ROOT . '/robots.txt';
$robotsContent = file_exists($robotsPath) ? file_get_contents($robotsPath) : "User-agent: *\nAllow: /\nSitemap: " . BASE_URL . "/sitemap.xml\n";
$sitemapExists = file_exists(APP_ROOT . '/sitemap.xml');

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">SEO Management</h1>
</div>

<?= renderFlash() ?>
<?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Global SEO Settings</h5></div>
            <div class="card-body">
                <form method="POST"><?= csrfField() ?>
                    <input type="hidden" name="action" value="save">
                    <div class="mb-3"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="<?= e($settings['meta_title'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="3"><?= e($settings['meta_description'] ?? '') ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Meta Keywords</label><input type="text" name="meta_keywords" class="form-control" value="<?= e($settings['meta_keywords'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Google Analytics ID</label><input type="text" name="google_analytics" class="form-control" value="<?= e($settings['google_analytics'] ?? '') ?>" placeholder="G-XXXXXXXXXX"></div>
                    <div class="mb-3"><label class="form-label">Google AdSense Code</label><textarea name="google_adsense" class="form-control font-monospace" rows="3"><?= e($settings['google_adsense'] ?? '') ?></textarea></div>
                    <div class="mb-3"><label class="form-label">robots.txt</label><textarea name="robots_txt" class="form-control font-monospace" rows="6"><?= e($robotsContent) ?></textarea></div>
                    <button type="submit" class="btn btn-danger">Save SEO Settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Sitemap</h5></div>
            <div class="card-body">
                <p class="text-muted small">Generate sitemap.xml for search engines including all published news and categories.</p>
                <?php if ($sitemapExists): ?>
                <div class="alert alert-success small">Sitemap exists. <a href="<?= url('sitemap.xml') ?>" target="_blank">View sitemap.xml</a></div>
                <?php else: ?>
                <div class="alert alert-warning small">No sitemap generated yet.</div>
                <?php endif; ?>
                <form method="POST"><?= csrfField() ?>
                    <input type="hidden" name="action" value="sitemap">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-diagram-3"></i> Generate Sitemap</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
