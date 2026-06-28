<?php
$pageTitle = 'About Us - ' . setting('site_name');
$metaDescription = 'About ' . setting('site_name');
require VIEWS_PATH . '/frontend/includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            <div class="bg-white p-4 p-md-5 rounded shadow-sm border">
                <!-- Premium About Banner -->
                <div class="bg-dark text-white p-4 p-md-5 rounded shadow-sm mb-5 position-relative overflow-hidden" style="border-left: 5px solid var(--primary-color) !important; margin: -3rem -3rem 3rem -3rem;">
                    <div class="position-relative z-1" style="z-index: 2;">
                        <h1 class="display-4 fw-bold font-merriweather mb-0"><i class="bi bi-info-circle text-danger me-3"></i>About Us</h1>
                    </div>
                    <i class="bi bi-building position-absolute text-white opacity-10" style="font-size: 15rem; right: -2rem; top: -3rem; transform: rotate(-15deg); z-index: 1;"></i>
                </div>
                <div class="article-content" style="font-size: 1.1rem; line-height: 1.8;">
                    <?= setting('about_us') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
