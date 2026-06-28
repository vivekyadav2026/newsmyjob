<?php
/**
 * Photo Gallery - Premium UI
 */
$pageTitle = 'Photo Gallery - ' . setting('site_name');
$db = Database::getInstance();
$stmt = $db->query('SELECT ni.*, n.title as news_title, n.slug FROM news_images ni INNER JOIN news n ON ni.news_id = n.id WHERE n.status = "published" ORDER BY ni.created_at DESC LIMIT 50');
$images = $stmt->fetchAll();
require VIEWS_PATH . '/frontend/includes/header.php';
?>

<div class="container my-5">
    <!-- Premium Gallery Banner -->
    <div class="bg-dark text-white p-4 p-md-5 rounded shadow-sm mb-5 position-relative overflow-hidden" style="border-left: 5px solid var(--primary-color) !important;">
        <div class="position-relative z-1" style="z-index: 2;">
            <h1 class="display-4 fw-bold font-merriweather mb-3"><i class="bi bi-images text-danger me-3"></i>Photo Gallery</h1>
            <p class="fs-5 text-light opacity-75 fst-italic mb-0">Explore the latest moments captured in pictures across all our stories.</p>
        </div>
        <i class="bi bi-images position-absolute text-white opacity-10" style="font-size: 15rem; right: -2rem; top: -3rem; transform: rotate(-15deg); z-index: 1;"></i>
    </div>

    <div class="masonry-gallery">
        <?php foreach ($images as $img): ?>
        <div class="masonry-item mb-4 position-relative overflow-hidden rounded shadow-sm">
            <a href="<?= newsUrl($img['slug']) ?>" class="d-block text-decoration-none">
                <img src="<?= UPLOADS_URL ?>/<?= e($img['image_path']) ?>" class="img-fluid w-100 hover-zoom" alt="<?= e($img['caption'] ?? $img['news_title']) ?>" loading="lazy">
                <div class="masonry-overlay position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                    <h6 class="text-white mb-0 font-merriweather"><?= e(truncate($img['news_title'], 50)) ?></h6>
                    <?php if ($img['caption']): ?>
                    <small class="text-light opacity-75"><?= e($img['caption']) ?></small>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .masonry-gallery {
        column-count: 1;
        column-gap: 1.5rem;
    }
    @media (min-width: 576px) { .masonry-gallery { column-count: 2; } }
    @media (min-width: 992px) { .masonry-gallery { column-count: 3; } }
    @media (min-width: 1200px) { .masonry-gallery { column-count: 4; } }
    
    .masonry-item { break-inside: avoid; }
    .hover-zoom { transition: transform 0.5s ease; }
    .masonry-item:hover .hover-zoom { transform: scale(1.08); }
    .masonry-overlay { opacity: 0; transition: opacity 0.3s ease; }
    .masonry-item:hover .masonry-overlay { opacity: 1; }
</style>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
