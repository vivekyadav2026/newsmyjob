<?php
/**
 * News Card Partial
 * Expects $article variable
 */
?>
<div class="col-md-<?= $col ?? '4' ?> mb-4">
    <div class="card news-card">
        <a href="<?= newsUrl($article['slug']) ?>">
            <?php if (!empty($article['featured_image'])): ?>
            <img src="<?= UPLOADS_URL ?>/<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
            <?php else: ?>
            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height:200px;"><i class="bi bi-image text-white fs-1"></i></div>
            <?php endif; ?>
        </a>
        <div class="card-body">
            <?php if (!empty($article['category_name'])): ?>
            <span class="category-badge"><?= e($article['category_name']) ?></span>
            <?php endif; ?>
            <h5><a href="<?= newsUrl($article['slug']) ?>"><?= e($article['title']) ?></a></h5>
            <p class="text-muted small mb-2"><?= e(truncate($article['excerpt'] ?? strip_tags($article['content'] ?? ''), 100)) ?></p>
            <div class="d-flex justify-content-between text-muted small">
                <span><i class="bi bi-person"></i> <?= e($article['author_name'] ?? 'Admin') ?></span>
                <span><i class="bi bi-clock"></i> <?= timeAgo($article['published_at'] ?? $article['created_at']) ?></span>
            </div>
        </div>
    </div>
</div>
