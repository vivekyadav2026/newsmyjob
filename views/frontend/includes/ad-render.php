<?php
/**
 * Advertisement Render Partial
 * Expects $ad variable
 */
if (empty($ad)) return;

if ($ad['ad_type'] === 'adsense' && !empty($ad['content'])):
    echo $ad['content'];
elseif (!empty($ad['content'])):
    echo $ad['content'];
elseif (!empty($ad['image'])):
    $link = $ad['link'] ?? '#';
    $clickUrl = BASE_URL . '/api/ad-click.php?id=' . (int) $ad['id'];
?>
<a href="<?= e($link) ?>" target="_blank" rel="noopener">
    <img src="<?= UPLOADS_URL ?>/<?= e($ad['image']) ?>" alt="<?= e($ad['title']) ?>" class="img-fluid">
</a>
<?php endif; ?>
