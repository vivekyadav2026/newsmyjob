<?php
$pageTitle = '404 Not Found - ' . setting('site_name');
require VIEWS_PATH . '/frontend/includes/header.php';
?>
<div class="container my-5 py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <h1 class="display-1 fw-bold text-danger font-merriweather mb-3">404</h1>
            <h3 class="fw-bold mb-4">Page Not Found</h3>
            <p class="text-muted fs-5 mb-5">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
            
            <form action="<?= BASE_URL ?>/search" method="GET" class="mb-5 d-flex gap-2 justify-content-center">
                <input type="text" name="q" class="form-control form-control-lg w-50 shadow-sm" placeholder="Search our site...">
                <button type="submit" class="btn btn-danger btn-lg px-4 shadow-sm"><i class="bi bi-search"></i></button>
            </form>
            
            <a href="<?= BASE_URL ?>" class="btn btn-dark btn-lg px-4 rounded-pill shadow-sm"><i class="bi bi-house-door me-2"></i>Back to Homepage</a>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
