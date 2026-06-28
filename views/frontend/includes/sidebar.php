<?php
/**
 * Premium Sidebar Widget Include
 */
$newsModel = new NewsModel();
$trendingNews = $newsModel->getTrending(5);
$mostViewed = $newsModel->getMostViewed(5);
$editorsPick = $newsModel->getEditorsPick(3);
$adModel = new AdvertisementModel();
$sidebarAds = $adModel->getByPosition('sidebar');
$categories = (new CategoryModel())->getAll('active');
?>

<!-- Newsletter Subscription -->
<div class="p-4 bg-light border rounded text-center mb-5" style="border-top: 4px solid var(--accent-color) !important;">
    <h5 class="fw-bold font-merriweather mb-2"><i class="bi bi-envelope-paper me-2 text-primary"></i>Newsletter</h5>
    <p class="text-muted small mb-3">Get the latest news and breaking updates delivered straight to your inbox.</p>
    <form class="d-flex flex-column gap-2">
        <input type="email" class="form-control text-center" placeholder="Your Email Address">
        <button class="btn btn-primary fw-bold w-100" style="background-color: var(--accent-color); border:none;">Subscribe</button>
    </form>
</div>

<!-- Trending News Numbered -->
<div class="mb-5">
    <div class="section-title mb-4">
        <h4>Trending Now</h4>
    </div>
    <div class="trending-numbered">
        <?php foreach ($trendingNews as $item): ?>
        <div class="trend-item">
            <div>
                <h6 class="font-merriweather"><a href="<?= newsUrl($item['slug']) ?>"><?= e(truncate($item['title'], 60)) ?></a></h6>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Advertisements -->
<?php foreach ($sidebarAds as $ad): ?>
<div class="ad-banner bg-light border rounded p-3 mb-5 text-center text-muted">
    <?php include VIEWS_PATH . '/frontend/includes/ad-render.php'; ?>
</div>
<?php endforeach; ?>

<!-- Editor's Pick -->
<?php if ($editorsPick): ?>
<div class="mb-5">
    <div class="section-title mb-4">
        <h4>Editor's Picks</h4>
    </div>
    <div>
        <?php foreach ($editorsPick as $item): ?>
        <div class="editor-pick-card">
            <a href="<?= newsUrl($item['slug']) ?>" class="d-block">
                <img src="<?= $item['featured_image'] ? UPLOADS_URL . '/' . e($item['featured_image']) : asset('images/placeholder.svg') ?>" alt="">
                <div class="caption">
                    <span class="badge bg-danger mb-2" style="font-size: 10px;"><?= e($item['category_name'] ?? 'Pick') ?></span>
                    <h6 class="font-merriweather"><?= e(truncate($item['title'], 55)) ?></h6>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Most Viewed -->
<div class="mb-5">
    <div class="section-title mb-4">
        <h4>Most Viewed</h4>
    </div>
    <div class="d-flex flex-column gap-3">
        <?php foreach ($mostViewed as $i => $item): ?>
        <div class="d-flex gap-3 align-items-center pb-3 <?= $i<count($mostViewed)-1 ? 'border-bottom' : '' ?>">
            <img src="<?= $item['featured_image'] ? UPLOADS_URL . '/' . e($item['featured_image']) : asset('images/placeholder.svg') ?>" class="rounded" style="width: 80px; height: 60px; object-fit: cover; flex-shrink: 0;">
            <div>
                <h6 class="mb-1 font-merriweather" style="font-size: 14px; font-weight: 700; line-height: 1.3;"><a href="<?= newsUrl($item['slug']) ?>" class="text-dark"><?= e(truncate($item['title'], 50)) ?></a></h6>
                <small class="text-muted" style="font-size: 12px;"><i class="bi bi-eye me-1"></i><?= number_format($item['views']) ?> Views</small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Categories -->
<div class="mb-5">
    <div class="section-title mb-4">
        <h4>Topics</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($categories as $cat): ?>
        <a href="<?= categoryUrl($cat['slug']) ?>" class="badge bg-white text-dark text-decoration-none border shadow-sm py-2 px-3 fw-bold" style="font-size: 13px;"><?= e($cat['name']) ?></a>
        <?php endforeach; ?>
    </div>
</div>
