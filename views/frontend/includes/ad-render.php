<?php
/**
 * Advertisement Render Partial
 * Expects $ad variable
 */
if (empty($ad)) return;

(new AdvertisementModel())->incrementImpression((int) $ad['id']);

if ($ad['ad_type'] === 'adsense' && !empty($ad['ad_code'])):
    echo $ad['ad_code'];
elseif (!empty($ad['ad_code'])):
    echo $ad['ad_code'];
elseif (!empty($ad['image'])):
    $link = $ad['link'] ?? '#';
    $clickUrl = BASE_URL . '/api/ad-click.php?id=' . (int) $ad['id'];
?>
<a href="<?= e($clickUrl) ?>" target="_blank" rel="noopener">
    <img src="<?= UPLOADS_URL ?>/<?= e($ad['image']) ?>" alt="<?= e($ad['title']) ?>" class="img-fluid" <?= $ad['width'] ? 'width="' . (int)$ad['width'] . '"' : '' ?>>
</a>
<?php endif; ?>
