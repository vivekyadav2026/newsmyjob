<?php
$pageTitle = 'About Us - ' . setting('site_name');
$metaDescription = 'About ' . setting('site_name');
require VIEWS_PATH . '/frontend/includes/header.php';
?>
<style>
.fancy-title-wrapper {
    position: relative;
    display: inline-block;
}
.fancy-title-wrapper::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 5px;
    background: var(--primary-color);
    border-radius: 10px;
}
.about-content-wrapper {
    font-size: 1.15rem;
    line-height: 2;
    color: #4a5568;
}
.about-content-wrapper h2, .about-content-wrapper h3 {
    color: #1a202c;
    font-weight: 800;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-family: 'Merriweather', serif;
}
.about-content-wrapper ul {
    list-style: none;
    padding-left: 0;
}
.about-content-wrapper ul li {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 1rem;
}
.about-content-wrapper ul li::before {
    content: '\F26A'; /* bootstrap icon chevron-right */
    font-family: 'bootstrap-icons';
    position: absolute;
    left: 0;
    top: 2px;
    color: var(--primary-color);
    font-size: 1.2rem;
}
</style>

<div class="container my-5 py-lg-4">
    <div class="text-center mb-5 pb-4">
        <div class="fancy-title-wrapper mx-auto mb-4">
            <h1 class="display-4 fw-black font-merriweather text-dark mb-0">About Us</h1>
        </div>
        <p class="fs-5 text-muted mt-4 mx-auto" style="max-width: 600px; line-height: 1.6;">Discover our story and the mission that drives us every day.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-lg border-0 position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100" style="height: 5px; background: linear-gradient(90deg, var(--primary-color), #ff6b6b);"></div>
                
                <div class="about-content-wrapper px-lg-4">
                    <?= setting('about_us') ?>
                </div>
                
            </div>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
