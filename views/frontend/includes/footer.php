<?php
/**
 * Frontend Footer Include - Premium UI
 */
$footerMenus = (new MenuModel())->getActive('footer');
$categories = (new CategoryModel())->getAll('active');
$adModel = new AdvertisementModel();
$footerAds = $adModel->getByPosition('footer');
?>
<footer class="site-footer">
    <div class="container">
        <?php foreach ($footerAds as $ad): ?>
            <div class="ad-banner mb-5 p-3 bg-dark bg-opacity-25 border border-secondary rounded text-center">
                <?php include VIEWS_PATH . '/frontend/includes/ad-render.php'; ?>
            </div>
        <?php endforeach; ?>

        <div class="row g-5">
            <!-- Col 1: About -->
            <div class="col-lg-3 col-md-6">
                <a href="<?= BASE_URL ?>" class="d-inline-block mb-4 text-decoration-none">
                    <?php if (setting('site_logo')): ?>
                        <img src="<?= UPLOADS_URL ?>/<?= e(setting('site_logo')) ?>" alt="<?= e(setting('site_name')) ?>" height="45">
                    <?php else: ?>
                        <h4 class="text-white fw-bold mb-0" style="font-family: 'Merriweather', serif;"><i class="bi bi-newspaper text-danger me-2"></i><?= e(setting('site_name', 'NewsMyJob')) ?></h4>
                    <?php endif; ?>
                </a>
                <p class="small text-muted" style="line-height: 1.8;"><?= e(setting('site_tagline', 'Your trusted source for breaking news, latest updates, and in-depth coverage from around the globe.')) ?></p>
                <div class="social-links d-flex gap-2 mt-4">
                    <?php foreach (['facebook' => 'facebook_url', 'twitter-x' => 'twitter_url', 'instagram' => 'instagram_url', 'youtube' => 'youtube_url'] as $icon => $key): ?>
                        <?php if (setting($key)): ?>
                        <a href="<?= e(setting($key)) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-<?= $icon ?>"></i></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Col 2: Categories -->
            <div class="col-lg-3 col-md-6">
                <h5>Sections</h5>
                <ul class="list-unstyled">
                    <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
                    <li><a href="<?= categoryUrl($cat['slug']) ?>"><i class="bi bi-chevron-right me-2" style="font-size: 10px;"></i><?= e($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Col 3: Quick Links -->
            <div class="col-lg-3 col-md-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <?php foreach ($footerMenus as $menu): ?>
                    <li><a href="<?= str_starts_with($menu['url'], 'http') ? e($menu['url']) : BASE_URL . e($menu['url']) ?>"><i class="bi bi-chevron-right me-2" style="font-size: 10px;"></i><?= e($menu['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Col 4: Contact & Newsletter -->
            <div class="col-lg-3 col-md-6">
                <h5>Newsletter</h5>
                <p class="small text-muted mb-3">Subscribe to our newsletter to get latest updates and news.</p>
                <form id="newsletterForm" class="mb-4">
                    <?= Security::csrfField() ?>
                    <div class="input-group">
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-white" placeholder="Email address" required>
                        <button class="btn btn-danger px-3" type="submit"><i class="bi bi-send"></i></button>
                    </div>
                </form>
                <h5 class="mt-4">Contact Us</h5>
                <p class="small text-muted mb-1"><i class="bi bi-envelope me-2 text-danger"></i><?= e(setting('site_email', 'info@newsmyjob.com')) ?></p>
                <p class="small text-muted"><i class="bi bi-telephone me-2 text-danger"></i><?= e(setting('site_phone', '+1 234 567 8900')) ?></p>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p class="mb-2 mb-md-0 text-muted">&copy; <?= date('Y') ?> <?= e(setting('site_name', 'NewsMyJob')) ?>. All rights reserved.</p>
            <p class="mb-0 text-muted small"><?= setting('footer_text') ?></p>
        </div>
    </div>
</footer>

<!-- Mobile Bottom Navigation -->
<div class="mobile-bottom-nav">
    <a href="<?= BASE_URL ?>" class="active"><i class="bi bi-house-door-fill"></i><span>Home</span></a>
    <a href="<?= BASE_URL ?>/latest"><i class="bi bi-lightning-fill"></i><span>Latest</span></a>
    <a href="<?= BASE_URL ?>/videos"><i class="bi bi-play-btn-fill"></i><span>Videos</span></a>
    <a href="#" data-bs-toggle="collapse" data-bs-target="#mainNav"><i class="bi bi-list"></i><span>Menu</span></a>
</div>

<?= setting('footer_code') ? setting('footer_code') : '' ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Automated Weather Fetching
    const weatherWidget = document.getElementById('weatherWidget');
    if (weatherWidget) {
        fetch('https://ipapi.co/json/')
            .then(response => response.json())
            .then(data => {
                if (data && data.latitude && data.longitude && data.city) {
                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${data.latitude}&longitude=${data.longitude}&current_weather=true`)
                        .then(res => res.json())
                        .then(weather => {
                            if (weather && weather.current_weather) {
                                weatherWidget.innerHTML = `<i class="bi bi-cloud-sun"></i> ${weather.current_weather.temperature}°C ${data.city}`;
                            }
                        })
                        .catch(err => {
                            weatherWidget.innerHTML = `<i class="bi bi-cloud-sun"></i> 28°C New Delhi`;
                        });
                } else {
                    weatherWidget.innerHTML = `<i class="bi bi-cloud-sun"></i> 28°C New Delhi`;
                }
            })
            .catch(err => {
                weatherWidget.innerHTML = `<i class="bi bi-cloud-sun"></i> 28°C New Delhi`;
            });
    }
});
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
