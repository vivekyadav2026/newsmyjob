<?php
/**
 * News Detail Page - Premium Redesign
 */
$slug = $_GET['slug'] ?? '';
$newsModel = new NewsModel();
$article = $newsModel->findBySlug($slug);

if (!$article || $article['status'] !== 'published') {
    http_response_code(404);
    require VIEWS_PATH . '/frontend/404.php';
    exit;
}

$newsModel->incrementViews((int) $article['id']);
$article['views']++;
trackPageView((int) $article['id'], 'news');

$tags = $newsModel->getTags((int) $article['id']);
$images = $newsModel->getImages((int) $article['id']);
$related = $newsModel->getRelated((int) $article['id'], $article['category_id'] ? (int) $article['category_id'] : null);
$prevNews = $newsModel->getAdjacent((int) $article['id'], 'prev');
$nextNews = $newsModel->getAdjacent((int) $article['id'], 'next');
$comments = (new CommentModel())->getByNewsId((int) $article['id']);
$inArticleAds = (new AdvertisementModel())->getByType('in_article');
$isBookmarked = false;
if (session_id()) {
    $isBookmarked = (new BookmarkModel())->isBookmarked(session_id(), (int) $article['id']);
}


$pageTitle = $article['meta_title'] ?: $article['title'];
$metaDescription = $article['meta_description'] ?: truncate(strip_tags($article['excerpt'] ?? $article['content']), 160);
$metaKeywords = $article['meta_keywords'] ?? '';
$canonicalUrl = $article['canonical_url'] ?: newsUrl($article['slug']);
$ogImage = $article['og_image'] ? UPLOADS_URL . '/' . $article['og_image'] : ($article['featured_image'] ? UPLOADS_URL . '/' . $article['featured_image'] : '');
$ogType = 'article';

$shareUrl = urlencode(newsUrl($article['slug']));
$shareTitle = urlencode($article['title']);

require VIEWS_PATH . '/frontend/includes/header.php';
?>

<article class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb fw-semibold" style="font-size: 13px;">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-dark"><i class="bi bi-house-door-fill"></i> Home</a></li>
            <?php if ($article['category_name']): ?>
            <li class="breadcrumb-item"><a href="<?= categoryUrl($article['category_slug']) ?>" class="text-danger"><?= e($article['category_name']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-muted text-truncate d-none d-sm-inline-block" style="max-width: 250px;"><?= e($article['title']) ?></li>
        </ol>
    </nav>

    <div class="row mt-4">
        <div class="col-lg-8">
            <header class="article-header mb-4">
                <h1 class="article-title"><?= e($article['title']) ?></h1>
                <p class="fs-5 text-muted font-merriweather fst-italic mb-4"><?= e($article['excerpt']) ?></p>
                
                <div class="article-meta-bar py-3 border-top border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3 bg-light px-3 rounded">
                    <div class="d-flex flex-column" style="font-size: 12px;">
                        <span><strong>Published:</strong> <?= formatDateTime($article['published_at']) ?></span>
                        <span><strong>Updated:</strong> <?= formatDateTime($article['updated_at'] ?? $article['published_at']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-clock me-1"></i> <?= $article['read_time'] ?> min read</span>
                        <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-eye me-1"></i> <?= number_format($article['views']) ?> views</span>
                    </div>
                </div>
            </header>

            <?php if ($article['featured_image']): ?>
            <figure class="mb-5 position-relative">
                <img src="<?= UPLOADS_URL ?>/<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>" class="article-featured-img shadow" style="width: 100%; max-height: 500px; object-fit: cover; border-radius: 8px;">
            </figure>
            <?php endif; ?>

            <div class="row">
                <!-- Floating Social Share -->
                <!-- <div class="col-lg-1 d-none d-lg-block">
                    <div class="sticky-share">
                        <a href="https://facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="btn text-white shadow-sm" style="background:#1877F2;"><i class="bi bi-facebook"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" class="btn text-white shadow-sm" style="background:#000000;"><i class="bi bi-twitter-x"></i></a>
                        <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" class="btn text-white shadow-sm" style="background:#25D366;"><i class="bi bi-whatsapp"></i></a>
                        <a href="https://linkedin.com/shareArticle?mini=true&url=<?= $shareUrl ?>" target="_blank" class="btn text-white shadow-sm" style="background:#0A66C2;"><i class="bi bi-linkedin"></i></a>
                        <button onclick="window.print()" class="btn btn-light border shadow-sm"><i class="bi bi-printer"></i></button>
                    </div>
                </div> -->
        
                <div class="col-lg-11">
            <!-- Font Size Controls & Share -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div class="d-flex gap-2">
                    <a href="https://facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" style="border-radius:20px; font-size:12px; background:#1877F2; border-color:#1877F2; color:#fff;" title="Share on Facebook">
                        <i class="bi bi-facebook"></i> <span class="d-none d-sm-inline">Facebook</span>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" class="btn btn-sm btn-outline-dark d-flex align-items-center gap-1" style="border-radius:20px; font-size:12px; background:#000; border-color:#000; color:#fff;" title="Share on X">
                        <i class="bi bi-twitter-x"></i> <span class="d-none d-sm-inline">X</span>
                    </a>
                    <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" style="border-radius:20px; font-size:12px; background:#25D366; border-color:#25D366; color:#fff;" title="Share on WhatsApp">
                        <i class="bi bi-whatsapp"></i> <span class="d-none d-sm-inline">WhatsApp</span>
                    </a>
                    <button id="webShareBtn" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="border-radius:20px; font-size:12px; background:#6c757d; border-color:#6c757d; color:#fff;" title="More Share Options">
                        <i class="bi bi-share-fill"></i> <span>Share</span>
                    </button>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted small fw-bold me-1 align-self-center">Text Size</span>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="document.querySelector('.article-content').style.fontSize='1rem'">A-</button>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="document.querySelector('.article-content').style.fontSize='1.15rem'">A</button>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="document.querySelector('.article-content').style.fontSize='1.3rem'">A+</button>
                </div>
            </div>

            <?php if ($article['youtube_embed'] || $article['video_url']): ?>
            <div class="ratio ratio-16x9 mb-5 rounded shadow overflow-hidden">
                <?php if ($article['youtube_embed']): ?>
                    <?= $article['youtube_embed'] ?>
                <?php else: ?>
                    <?php $ytId = getYoutubeId($article['video_url']); ?>
                    <?php if ($ytId): ?><iframe src="https://www.youtube.com/embed/<?= e($ytId) ?>" allowfullscreen></iframe><?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="article-content" style="font-size: 1.15rem;">
                <?= $article['content'] ?>
            </div>

            <?php foreach ($inArticleAds as $ad): ?>
            <div class="ad-banner my-5 p-4 bg-light border text-center text-muted rounded shadow-sm">
                <?php include VIEWS_PATH . '/frontend/includes/ad-render.php'; ?>
            </div>
            <?php endforeach; ?>

            <?php if ($tags): ?>
            <div class="mt-5 pt-4 border-top">
                <h6 class="fw-bold mb-3"><i class="bi bi-tags me-2 text-danger"></i>Article Tags</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($tags as $tag): ?>
                    <a href="#" class="badge bg-light text-dark border p-2 text-decoration-none shadow-sm hover-lift"><?= e($tag['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Prev/Next -->
            <div class="row mt-5 pt-4 border-top g-3 g-md-4">
                <div class="col-12 col-md-6">
                    <?php if ($prevNews): ?>
                    <a href="<?= newsUrl($prevNews['slug']) ?>" class="d-flex flex-column bg-white p-3 p-md-4 text-decoration-none shadow-sm hover-lift text-start h-100" style="border: 1px solid #E5E7EB; border-left: 4px solid var(--primary-color); border-radius: 12px;">
                        <div class="d-flex align-items-center mb-2 mb-md-3">
                            <i class="bi bi-arrow-left text-danger me-2" style="font-size: 1.1rem;"></i>
                            <small class="text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 11px;">Previous Story</small>
                        </div>
                        <span class="text-dark fw-bold font-merriweather lh-base d-block hover-danger" style="font-size: clamp(1rem, 2vw, 1.25rem);"><?= e(truncate($prevNews['title'], 65)) ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6">
                    <?php if ($nextNews): ?>
                    <a href="<?= newsUrl($nextNews['slug']) ?>" class="d-flex flex-column bg-white p-3 p-md-4 text-decoration-none shadow-sm hover-lift text-start text-md-end h-100 article-next-card" style="border: 1px solid #E5E7EB; border-radius: 12px;">
                        <div class="d-flex align-items-center justify-content-start justify-content-md-end mb-2 mb-md-3">
                            <small class="text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 11px;">Next Story</small>
                            <i class="bi bi-arrow-right text-danger ms-2" style="font-size: 1.1rem;"></i>
                        </div>
                        <span class="text-dark fw-bold font-merriweather lh-base d-block hover-danger" style="font-size: clamp(1rem, 2vw, 1.25rem);"><?= e(truncate($nextNews['title'], 65)) ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Gallery -->
            <?php if ($images): ?>
            <div class="mt-5 p-4 bg-light rounded border">
                <h5 class="fw-bold font-merriweather mb-4"><i class="bi bi-images text-danger me-2"></i>Photo Gallery</h5>
                <div class="row g-3">
                    <?php foreach ($images as $img): ?>
                    <div class="col-md-4 col-6">
                        <a href="<?= UPLOADS_URL ?>/<?= e($img['image_path']) ?>" target="_blank">
                            <img src="<?= UPLOADS_URL ?>/<?= e($img['image_path']) ?>" class="img-fluid rounded shadow-sm hover-zoom" alt="<?= e($img['caption'] ?? '') ?>" style="height: 120px; object-fit: cover; width: 100%;">
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comments -->
            <?php if (setting('enable_comments') === '1'): ?>
            <div class="mt-5 p-4 border rounded shadow-sm bg-white">
                <h4 class="section-title mb-4">Comments (<?= count($comments) ?>)</h4>
                <?php foreach ($comments as $comment): ?>
                <div class="d-flex gap-3 mb-4 pb-3 border-bottom">
                    <img src="<?= asset('images/placeholder.svg') ?>" class="rounded-circle" style="width: 40px; height: 40px;">
                    <div>
                        <div class="fw-bold"><?= e($comment['name']) ?> <small class="text-muted ms-2 fw-normal"><?= timeAgo($comment['created_at']) ?></small></div>
                        <p class="mb-0 mt-2 text-dark"><?= e($comment['comment']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <h5 class="fw-bold mt-5 mb-3">Leave a Reply</h5>
                <p class="text-muted small">Your email address will not be published. Required fields are marked *</p>
                <form id="commentForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="news_id" value="<?= $article['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" name="name" class="form-control bg-light" placeholder="Your Name *" required></div>
                        <div class="col-md-6"><input type="email" name="email" class="form-control bg-light" placeholder="Your Email *" required></div>
                        <div class="col-12"><textarea name="comment" class="form-control bg-light" rows="4" placeholder="Write your comment here..." required></textarea></div>
                        <div class="col-12"><button type="submit" class="btn btn-danger px-4 py-2 fw-bold rounded-pill shadow-sm">Post Comment</button></div>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Related News -->
            <?php if ($related): ?>
            <div class="mt-5">
                <div class="section-title">
                    <h4>More from <?= e($article['category_name']) ?></h4>
                </div>
                <div class="row g-4">
                    <?php foreach ($related as $rel): ?>
                    <div class="col-md-6">
                        <?php $article_rel = $rel; $article_rel['category_name'] = $article['category_name']; ?>
                        <?php $article = $article_rel; include VIEWS_PATH . '/frontend/includes/news-card-grid.php'; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        </div>
        </div>
        
        <div class="col-lg-4 mt-5 mt-lg-0">
            <?php require VIEWS_PATH . '/frontend/includes/sidebar.php'; ?>
        </div>
    </div>
</article>

<style>
    .hover-lift { transition: transform 0.2s; }
    .hover-lift:hover { transform: translateY(-3px); }
    .hover-danger:hover { color: var(--primary-color) !important; text-decoration: underline !important; }
    .hover-zoom { transition: transform 0.4s; }
    .hover-zoom:hover { transform: scale(1.05); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const webShareBtn = document.getElementById('webShareBtn');
    if (webShareBtn) {
        if (navigator.share) {
            webShareBtn.addEventListener('click', async () => {
                try {
                    await navigator.share({
                        title: decodeURIComponent('<?= $shareTitle ?>'),
                        url: decodeURIComponent('<?= $shareUrl ?>')
                    });
                } catch (err) {
                    console.log('Share canceled or failed', err);
                }
            });
        } else {
            webShareBtn.addEventListener('click', () => {
                const tempInput = document.createElement('input');
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                
                // Show a clean visual overlay notification instead of ugly alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-4 px-4 py-2 bg-dark text-white rounded shadow';
                alertDiv.style.zIndex = '9999';
                alertDiv.style.fontSize = '14px';
                alertDiv.innerHTML = '<i class="bi bi-check-circle-fill text-success me-2"></i> Link copied to clipboard!';
                document.body.appendChild(alertDiv);
                setTimeout(() => { alertDiv.remove(); }, 3000);
            });
        }
    }
});
</script>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
