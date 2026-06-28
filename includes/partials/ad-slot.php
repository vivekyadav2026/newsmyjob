<?php
/**
 * Render advertisements for a position
 */
declare(strict_types=1);

if (!isset($position)) {
    return;
}

$ads = (new AdvertisementModel())->getByPosition($position);
if (empty($ads)) {
    return;
}
?>
<div class="ad-slot ad-slot-<?= e($position) ?> mb-4">
    <?php foreach ($ads as $ad): ?>
    <div class="ad-item text-center">
        <?php if ($ad['ad_type'] === 'image' && !empty($ad['image'])): ?>
            <?php if (!empty($ad['link'])): ?><a href="<?= e($ad['link']) ?>" target="_blank" rel="noopener sponsored"><?php endif; ?>
            <img src="<?= uploadUrl($ad['image']) ?>" alt="<?= e($ad['title']) ?>" class="img-fluid ad-image" loading="lazy">
            <?php if (!empty($ad['link'])): ?></a><?php endif; ?>
        <?php elseif (!empty($ad['content'])): ?>
            <?= $ad['content'] ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
