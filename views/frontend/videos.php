<?php
/**
 * Videos Page - Premium UI
 */
$pageTitle = 'Videos - ' . setting('site_name');
$newsModel = new NewsModel();
$videos = $newsModel->getVideos(20);
require VIEWS_PATH . '/frontend/includes/header.php';
?>

<div class="container my-5">
    <div class="page-header mb-5 pb-3 border-bottom border-2" style="border-color: var(--primary-color) !important;">
        <h1 class="display-5 fw-bold font-merriweather text-dark mb-2"><i class="bi bi-play-circle-fill text-danger me-3"></i>Video News</h1>
        <p class="fs-6 text-muted mb-0">Watch the latest news coverages, exclusive interviews, and breaking reports.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($videos as $article): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm video-card">
                <div class="ratio ratio-16x9 bg-dark position-relative overflow-hidden rounded-top">
                    <?php if ($article['youtube_embed']): ?>
                        <?= $article['youtube_embed'] ?>
                    <?php else: ?>
                        <?php $ytId = getYoutubeId($article['video_url'] ?? ''); ?>
                        <?php if ($ytId): ?>
                            <img src="https://img.youtube.com/vi/<?= e($ytId) ?>/maxresdefault.jpg" class="w-100 h-100 object-fit-cover opacity-75">
                            <div class="position-absolute top-50 start-50 translate-middle pointer-events-none">
                                <i class="bi bi-youtube text-danger" style="font-size: 3rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></i>
                            </div>
                            <a href="<?= newsUrl($article['slug']) ?>" class="stretched-link"></a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title font-merriweather fw-bold"><a href="<?= newsUrl($article['slug']) ?>" class="text-dark text-decoration-none hover-danger"><?= e($article['title']) ?></a></h5>
                    <div class="mt-3 text-muted small d-flex justify-content-between">
                        <span><i class="bi bi-clock me-1"></i><?= timeAgo($article['published_at']) ?></span>
                        <span><i class="bi bi-eye me-1"></i><?= number_format($article['views']) ?> views</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .video-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .video-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .hover-danger:hover { color: var(--primary-color) !important; text-decoration: underline !important; }
    .pointer-events-none { pointer-events: none; }
</style>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
