<?php
/**
 * News card partial
 * @var array $item News row
 * @var string $size default|small|featured
 */
declare(strict_types=1);

if (empty($item)) {
    return;
}

$size = $size ?? 'default';
$cardClass = $size === 'featured' ? 'news-card news-card-featured' : ($size === 'small' ? 'news-card news-card-sm' : 'news-card');
?>
<article class="<?= $cardClass ?>">
    <a href="<?= newsUrl($item['slug']) ?>" class="news-card-link">
        <div class="news-card-image">
            <img src="<?= uploadUrl($item['featured_image'] ?? '') ?>" alt="<?= e($item['title']) ?>" loading="lazy">
            <?php if (!empty($item['category_name'])): ?>
            <span class="news-card-category"><?= e($item['category_name']) ?></span>
            <?php endif; ?>
        </div>
        <div class="news-card-body">
            <h3 class="news-card-title"><?= e($item['title']) ?></h3>
            <?php if ($size !== 'small' && !empty($item['excerpt'])): ?>
            <p class="news-card-excerpt"><?= e(truncate($item['excerpt'], 120)) ?></p>
            <?php endif; ?>
            <div class="news-card-meta">
                <span><i class="bi bi-clock"></i> <?= timeAgo($item['published_at'] ?? $item['created_at']) ?></span>
                <?php if (!empty($item['views'])): ?>
                <span><i class="bi bi-eye"></i> <?= number_format((int) $item['views']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</article>
