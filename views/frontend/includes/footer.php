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
                        <img src="<?= UPLOADS_URL ?>/<?= e(setting('site_logo')) ?>" alt="<?= e(setting('site_name')) ?>" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover; border: 3px solid rgba(255,255,255,0.1);">
                    <?php else: ?>
                        <img src="<?= asset('logo/footer_logo.png') ?>" alt="<?= e(setting('site_name', 'NewsMyJob')) ?>" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover; border: 3px solid rgba(255,255,255,0.1);">
                    <?php endif; ?>
                </a>
                <p class="small text-light text-opacity-75" style="line-height: 1.8;"><?= e(setting('site_tagline', 'Your trusted source for breaking news, latest updates, and in-depth coverage from around the globe.')) ?></p>
                <div class="social-links d-flex gap-2 mt-4 flex-wrap">
                    <a href="https://x.com/Toolxy5" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center text-opacity-75 border-secondary" style="width: 36px; height: 36px;" title="X (Twitter)"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.instagram.com/toolxy.in?igsh=dHIwNnMyajdicnN3" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center text-opacity-75 border-secondary" style="width: 36px; height: 36px;" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.linkedin.com/in/md-anish-35122540b" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center text-opacity-75 border-secondary" style="width: 36px; height: 36px;" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="https://www.threads.net/@toolxy.in" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center text-opacity-75 border-secondary" style="width: 36px; height: 36px;" title="Threads"><i class="bi bi-threads"></i></a>
                    <a href="https://pin.it/2nsgB5SIm" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center text-opacity-75 border-secondary" style="width: 36px; height: 36px;" title="Pinterest"><i class="bi bi-pinterest"></i></a>
                    <a href="https://www.quora.com/profile/Md-Anish-290" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-flex align-items-center justify-content-center text-opacity-75 border-secondary" style="width: 36px; height: 36px;" title="Quora"><i class="bi bi-quora"></i></a>
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
                    <?php if ($footerMenus): ?>
                        <?php foreach ($footerMenus as $menu): ?>
                        <li><a href="<?= str_starts_with($menu['url'], 'http') ? e($menu['url']) : BASE_URL . e($menu['url']) ?>"><i class="bi bi-chevron-right me-2" style="font-size: 10px;"></i><?= e($menu['title']) ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/about"><i class="bi bi-chevron-right me-2" style="font-size: 10px;"></i>About Us</a></li>
                        <li><a href="<?= BASE_URL ?>/contact"><i class="bi bi-chevron-right me-2" style="font-size: 10px;"></i>Contact Us</a></li>
                        <li><a href="<?= BASE_URL ?>/terms"><i class="bi bi-chevron-right me-2" style="font-size: 10px;"></i>Terms & Conditions</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Col 4: Contact & Newsletter -->
            <div class="col-lg-3 col-md-6">
                <h5>Newsletter</h5>
                <p class="small text-light text-opacity-75 mb-3">Subscribe to our newsletter to get latest updates and news.</p>
                <form id="newsletterForm" class="mb-4">
                    <?= Security::csrfField() ?>
                    <div class="input-group shadow-sm" style="border-radius: 6px; overflow: hidden;">
                        <input type="email" name="email" class="form-control bg-dark border-0 text-white" placeholder="Email address" style="font-size: 14px; padding: 12px 15px;" required>
                        <button class="btn btn-primary px-4 fw-bold" type="submit" style="background-color: var(--primary-color); border:none;"><i class="bi bi-send me-2"></i>Subscribe</button>
                    </div>
                </form>
                <h5 class="mt-4">Contact Us</h5>
                <p class="small text-light text-opacity-75 mb-1"><i class="bi bi-envelope me-2 text-danger"></i><?= e(setting('site_email', 'info@newsmyjob.com')) ?></p>
                <p class="small text-light text-opacity-75"><i class="bi bi-telephone me-2 text-danger"></i><?= e(setting('site_phone', '+1 234 567 8900')) ?></p>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center text-light text-opacity-75">
            <p class="mb-2 mb-md-0">&copy; <?= date('Y') ?> <?= e(setting('site_name', 'NewsMyJob')) ?>. All rights reserved.</p>
            <p class="mb-0 small"><?= setting('footer_text') ?></p>
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

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="shareModalLabel">Share Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="#" id="shareFacebook" target="_blank" class="btn text-white rounded-circle d-flex align-items-center justify-content-center share-btn-social" style="width: 50px; height: 50px; background:#1877F2;"><i class="bi bi-facebook fs-4"></i></a>
                    <a href="#" id="shareTwitter" target="_blank" class="btn text-white rounded-circle d-flex align-items-center justify-content-center share-btn-social" style="width: 50px; height: 50px; background:#000000;"><i class="bi bi-twitter-x fs-4"></i></a>
                    <a href="#" id="shareWhatsapp" target="_blank" class="btn text-white rounded-circle d-flex align-items-center justify-content-center share-btn-social" style="width: 50px; height: 50px; background:#25D366;"><i class="bi bi-whatsapp fs-4"></i></a>
                    <a href="#" id="shareTelegram" target="_blank" class="btn text-white rounded-circle d-flex align-items-center justify-content-center share-btn-social" style="width: 50px; height: 50px; background:#0088cc;"><i class="bi bi-telegram fs-4"></i></a>
                    <a href="#" id="shareLinkedin" target="_blank" class="btn text-white rounded-circle d-flex align-items-center justify-content-center share-btn-social" style="width: 50px; height: 50px; background:#0A66C2;"><i class="bi bi-linkedin fs-4"></i></a>
                </div>
                <div class="input-group mt-4 shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <input type="text" id="shareLinkInput" class="form-control border-0 bg-light" readonly>
                    <button class="btn btn-primary px-3 fw-bold" type="button" id="copyShareLink" style="background-color: var(--primary-color); border:none;"><i class="bi bi-files me-1"></i> Copy</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
