<?php
/**
 * Admin - SEO & Sitemap
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('seo');

$pageTitle = 'SEO & Sitemap';
$settingsModel = new SettingsModel();
$newsModel = new NewsModel();
$categoryModel = new CategoryModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/seo.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'robots') {
        $robotsTxt = $_POST['robots_txt'] ?? '';
        $settingsModel->set('robots_txt', $robotsTxt, 'seo');

        $robotsPath = APP_ROOT . '/robots.txt';
        file_put_contents($robotsPath, $robotsTxt);

        ActivityLogModel::log(Auth::id(), 'update', 'seo', 'Updated robots.txt');
        Session::flash('success', 'robots.txt saved successfully.');
        redirect(ADMIN_URL . '/seo.php');
    }

    if ($action === 'sitemap') {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $staticPages = ['', 'about-us', 'contact-us', 'videos', 'gallery'];
        foreach ($staticPages as $page) {
            $loc = rtrim(BASE_URL, '/') . '/' . ltrim($page, '/');
            $xml .= "  <url><loc>" . htmlspecialchars($loc) . "</loc><changefreq>daily</changefreq><priority>0.8</priority></url>\n";
        }

        foreach ($categoryModel->getAll('active') as $cat) {
            $loc = categoryUrl($cat['slug']);
            $xml .= "  <url><loc>" . htmlspecialchars($loc) . "</loc><changefreq>daily</changefreq><priority>0.7</priority></url>\n";
        }

        $newsResult = $newsModel->getAll(['status' => 'published'], 1, 5000);
        foreach ($newsResult['data'] as $article) {
            $loc = newsUrl($article['slug']);
            $lastmod = date('Y-m-d', strtotime($article['updated_at'] ?? $article['created_at']));
            $xml .= "  <url><loc>" . htmlspecialchars($loc) . "</loc><lastmod>{$lastmod}</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>\n";
        }

        $xml .= '</urlset>';

        $sitemapPath = APP_ROOT . '/sitemap.xml';
        file_put_contents($sitemapPath, $xml);

        ActivityLogModel::log(Auth::id(), 'create', 'seo', 'Generated sitemap.xml');
        Session::flash('success', 'Sitemap generated successfully at ' . BASE_URL . '/sitemap.xml');
        redirect(ADMIN_URL . '/seo.php');
    }
}

$robotsTxt = $settingsModel->get('robots_txt', "User-agent: *\nAllow: /\nSitemap: " . BASE_URL . "/sitemap.xml");
$sitemapExists = file_exists(APP_ROOT . '/sitemap.xml');
$sitemapModified = $sitemapExists ? date('M d, Y h:i A', filemtime(APP_ROOT . '/sitemap.xml')) : null;

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">SEO & Sitemap</h4>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="content-card mb-4">
                    <h5 class="mb-3"><i class="bi bi-diagram-3 me-2"></i>Sitemap Generator</h5>
                    <p class="text-muted">Generate an XML sitemap including all published articles, categories, and static pages.</p>
                    <?php if ($sitemapExists): ?>
                    <div class="alert alert-info py-2">
                        <i class="bi bi-check-circle me-1"></i> Sitemap exists — last modified: <?= $sitemapModified ?>
                        <br><a href="<?= BASE_URL ?>/sitemap.xml" target="_blank" class="small"><?= BASE_URL ?>/sitemap.xml</a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning py-2">No sitemap found. Generate one below.</div>
                    <?php endif; ?>
                    <form method="POST">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="sitemap">
                        <button type="submit" class="btn btn-danger"><i class="bi bi-arrow-repeat me-1"></i> Generate sitemap.xml</button>
                    </form>
                </div>

                <div class="content-card">
                    <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>SEO Tips</h5>
                    <ul class="small text-muted mb-0">
                        <li>Submit your sitemap to Google Search Console</li>
                        <li>Keep meta titles under 60 characters</li>
                        <li>Write unique meta descriptions for each article</li>
                        <li>Use descriptive slugs for better indexing</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="content-card">
                    <h5 class="mb-3"><i class="bi bi-robot me-2"></i>robots.txt Editor</h5>
                    <form method="POST">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="robots">
                        <div class="mb-3">
                            <textarea name="robots_txt" class="form-control font-monospace" rows="12"><?= e($robotsTxt) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-save me-1"></i> Save robots.txt</button>
                        <a href="<?= BASE_URL ?>/robots.txt" target="_blank" class="btn btn-outline-secondary ms-2">View Live</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
