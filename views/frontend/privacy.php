<?php
$pageTitle = 'Privacy Policy - ' . setting('site_name');
require VIEWS_PATH . '/frontend/includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            <div class="bg-white p-4 p-md-5 rounded shadow-sm border">
                <div class="page-header mb-4 pb-3 border-bottom border-2 text-center" style="border-color: var(--primary-color) !important;">
                    <h1 class="display-5 fw-bold font-merriweather text-dark mb-0"><i class="bi bi-shield-lock text-danger me-3"></i>Privacy Policy</h1>
                </div>
                <div class="article-content" style="font-size: 1.1rem; line-height: 1.8;">
                    <?= setting('privacy_policy') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
