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
<div class="p-4 border rounded text-center mb-5 shadow-sm" style="background: var(--card-bg); border-top: 4px solid var(--accent-color) !important;">
    <h5 class="fw-bold font-merriweather mb-3"><i class="bi bi-envelope-paper-fill me-2" style="color: var(--accent-color);"></i>Newsletter</h5>
    <p class="text-muted small mb-4" style="line-height: 1.6;">Get the latest news and breaking updates delivered straight to your inbox.</p>
    <form class="d-flex flex-column gap-3">
        <input type="email" class="form-control form-control-lg rounded-pill text-center shadow-sm border" placeholder="Your Email Address" style="font-size: 14px;">
        <button class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm w-100" style="background-color: var(--accent-color); border:none; font-size: 15px; transition: var(--transition);">Subscribe</button>
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
        <div class="news-list-card">
            <div class="img-wrapper">
                <a href="<?= newsUrl($item['slug']) ?>">
                    <img src="<?= $item['featured_image'] ? UPLOADS_URL . '/' . e($item['featured_image']) : asset('images/placeholder.svg') ?>" alt="<?= e(truncate($item['title'], 50)) ?>">
                </a>
            </div>
            <div class="content">
                <h6><a href="<?= newsUrl($item['slug']) ?>"><?= e(truncate($item['title'], 50)) ?></a></h6>
                <div class="meta"><i class="bi bi-eye me-1"></i><?= number_format($item['views']) ?> Views</div>
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
        <a href="<?= categoryUrl($cat['slug']) ?>" class="category-badge text-decoration-none"><?= e($cat['name']) ?></a>
        <?php endforeach; ?>
    </div>
</div>
