<?php
/**
 * Horizontal News Card Partial
 * Expects $article
 */
?>
<div class="news-card-hz">
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
        <p class="text-muted small mb-2 d-none d-md-block"><?= e(truncate($article['excerpt'] ?? strip_tags($article['content'] ?? ''), 120)) ?></p>
        <div class="meta d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-clock me-1"></i><?= timeAgo($article['published_at'] ?? $article['created_at']) ?>
                <span class="mx-2 text-muted">|</span>
                <i class="bi bi-eye me-1"></i><?= number_format($article['views'] ?? 0) ?>
            </div>
            <div class="actions">
                <a href="#" class="text-muted me-2"><i class="bi bi-share"></i></a>
                <a href="#" class="text-muted"><i class="bi bi-bookmark"></i></a>
            </div>
        </div>
    </div>
</div>
