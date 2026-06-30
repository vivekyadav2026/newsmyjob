<?php
/**
 * Homepage - Premium UI Redesign
 */
$newsModel = new NewsModel();
$categoryModel = new CategoryModel();
$adModel = new AdvertisementModel();

$featured = $newsModel->getFeatured(5);
$latest = $newsModel->getPublished([], 1, 10);
$trending = $newsModel->getTrending(6);
$editorsPick = $newsModel->getEditorsPick(4);
$mostViewed = $newsModel->getMostViewed(6);

$categories = $categoryModel->getAll('active');
$headerAds = $adModel->getByPosition('header');
$popupAds = $adModel->getByType('popup');

$pageTitle = setting('meta_title', setting('site_name'));
$metaDescription = setting('meta_description', '');

trackPageView(null, 'home');

require VIEWS_PATH . '/frontend/includes/header.php';
?>

<?php foreach ($headerAds as $ad): ?>
<div class="container mt-3"><div class="ad-banner"><?php include VIEWS_PATH . '/frontend/includes/ad-render.php'; ?></div></div>
<?php endforeach; ?>

<div class="container mt-4 hero-wrapper">
    <div class="row g-4">
        <!-- Main Featured 70% -->
        <div class="col-lg-8">
            <?php if (!empty($featured[0])): $mainFeature = $featured[0]; ?>
            <div class="hero-main shadow-sm">
                <a href="<?= newsUrl($mainFeature['slug']) ?>" class="d-block h-100">
                    <img src="<?= $mainFeature['featured_image'] ? UPLOADS_URL . '/' . e($mainFeature['featured_image']) : asset('images/placeholder.svg') ?>" alt="<?= e($mainFeature['title']) ?>">
                    <div class="hero-caption">
                        <span class="badge bg-danger mb-3 px-3 py-2 text-uppercase fw-bold shadow" style="font-size: 0.8rem; letter-spacing: 1px;"><?= e($mainFeature['category_name'] ?? 'Top Story') ?></span>
                        <h1 class="headline"><a href="<?= newsUrl($mainFeature['slug']) ?>"><?= e($mainFeature['title']) ?></a></h1>
                        <p class="d-none d-md-block"><?= e(truncate($mainFeature['excerpt'] ?? strip_tags($mainFeature['content']), 150)) ?></p>
                        <div class="meta">
                            <span><i class="bi bi-clock me-1"></i><?= timeAgo($mainFeature['published_at']) ?></span>
                            <span><i class="bi bi-eye me-1"></i><?= number_format($mainFeature['views'] ?? 0) ?> Views</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Top Stories 30% -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <div class="p-3 bg-light border rounded" style="border-top: 4px solid var(--primary-color) !important;">
                <h5 class="fw-bold mb-3 font-merriweather"><span class="live-badge me-2">LIVE</span> Top Stories</h5>
                <div class="d-flex flex-column gap-3">
                    <?php for($i=1; $i<min(4, count($featured)); $i++): $item = $featured[$i]; ?>
                    <div class="news-list-card">
                        <div class="img-wrapper">
                            <a href="<?= newsUrl($item['slug']) ?>">
                                <img src="<?= $item['featured_image'] ? UPLOADS_URL . '/' . e($item['featured_image']) : asset('images/placeholder.svg') ?>" alt="<?= e($item['title']) ?>">
                            </a>
                        </div>
                        <div class="content">
                            <h6><a href="<?= newsUrl($item['slug']) ?>"><?= e($item['title']) ?></a></h6>
                            <div class="meta text-danger"><i class="bi bi-clock me-1"></i><?= timeAgo($item['published_at']) ?></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="ad-banner bg-light border rounded flex-grow-1 d-flex align-items-center justify-content-center text-muted">
                <small>Advertisement (300x250)</small>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4">
        <!-- Main Content 70% -->
        <div class="col-lg-8">
            
            <!-- Latest News (Horizontal) -->
            <div class="section-title">
                <h4>Latest News</h4>
                <a href="<?= BASE_URL ?>/latest" class="view-all">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="mb-5">
                <?php foreach (array_slice($latest['data'], 0, 5) as $article): ?>
                    <?php include VIEWS_PATH . '/frontend/includes/news-card-hz.php'; ?>
                <?php endforeach; ?>
                <div class="text-center mt-4">
                    <button class="btn btn-outline-danger fw-bold rounded-pill px-4 py-2" id="loadMoreLatest"><i class="bi bi-arrow-clockwise me-1"></i> Load More</button>
                </div>
            </div>

            <!-- Category Highlights -->
            <?php foreach ($categoryModel->getHomeCategories() as $cat): ?>
                <?php $catNews = $newsModel->getByCategory((int)$cat['id'], 5); ?>
                <?php if (count($catNews) > 0): ?>
                <div class="section-title mt-5">
                    <h4><?= e($cat['name']) ?></h4>
                    <a href="<?= categoryUrl($cat['slug']) ?>" class="view-all">More in <?= e($cat['name']) ?> <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <?php $article = $catNews[0]; $article['category_name'] = $cat['name']; include VIEWS_PATH . '/frontend/includes/news-card-grid.php'; ?>
                    </div>
                    <div class="col-md-6 d-flex flex-column gap-3">
                        <?php for($i=1; $i<min(5, count($catNews)); $i++): $item = $catNews[$i]; ?>
                        <div class="news-list-card">
                            <div class="img-wrapper">
                                <a href="<?= newsUrl($item['slug']) ?>">
                                    <img src="<?= $item['featured_image'] ? UPLOADS_URL . '/' . e($item['featured_image']) : asset('images/placeholder.svg') ?>" alt="<?= e($item['title']) ?>">
                                </a>
                            </div>
                            <div class="content">
                                <h6><a href="<?= newsUrl($item['slug']) ?>"><?= e($item['title']) ?></a></h6>
                                <div class="meta"><i class="bi bi-clock me-1"></i><?= timeAgo($item['published_at']) ?></div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>


            
            <div class="ad-banner bg-light border p-4 text-center text-muted rounded mb-5">
                Advertisement Leaderboard (728x90)
            </div>

        </div>

        <!-- Sidebar 30% -->
        <div class="col-lg-4">
            <?php require VIEWS_PATH . '/frontend/includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php if ($popupAds): ?>
<?php $ad = $popupAds[0]; ?>
<div class="modal fade" id="popupAd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center"><?php include VIEWS_PATH . '/frontend/includes/ad-render.php'; ?></div>
        </div>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('popupAd')).show(); });</script>
<?php endif; ?>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
