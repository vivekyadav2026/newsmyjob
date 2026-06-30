<?php
/**
 * Grid News Card Partial (Premium)
 * Expects $article
 */
$isBookmarked = Auth::check() ? (new BookmarkModel())->isBookmarked(session_id(), (int)$article['id'], Auth::id()) : false;
$shareUrl = newsUrl($article['slug']);
$shareTitle = e($article['title']);
?>
<div class="news-card-grid">
    <div class="img-wrapper">
        <a href="<?= newsUrl($article['slug']) ?>">
            <img src="<?= $article['featured_image'] ? UPLOADS_URL . '/' . e($article['featured_image']) : asset('images/placeholder.svg') ?>" alt="<?= e($article['title']) ?>" loading="lazy">
        </a>
    </div>
    <div class="content">
        <?php if (!empty($article['category_name'])): ?>
        <div class="category"><?= e($article['category_name']) ?></div>
        <?php endif; ?>
        <h5><a href="<?= newsUrl($article['slug']) ?>"><?= e($article['title']) ?></a></h5>
        <div class="meta text-muted mt-2 d-flex justify-content-between">
            <small><i class="bi bi-clock me-1"></i><?= timeAgo($article['published_at'] ?? $article['created_at']) ?></small>
            <div class="actions">
                <a href="#" class="text-muted me-2 btn-share" data-share-url="<?= $shareUrl ?>" data-share-title="<?= $shareTitle ?>" title="Share"><i class="bi bi-share"></i></a>
                <a href="#" class="<?= $isBookmarked ? 'text-primary' : 'text-muted' ?> btn-bookmark" data-news-id="<?= $article['id'] ?>" title="Save">
                    <i class="bi <?= $isBookmarked ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i>
                </a>
            </div>
        </div>
    </div>
</div>
