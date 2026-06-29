<?php
/**
 * Contact Us Page - Premium UI
 */
$pageTitle = 'Contact Us - ' . setting('site_name');
require VIEWS_PATH . '/frontend/includes/header.php';
?>

<div class="container my-5">
    <div class="page-header mb-5 pb-3 border-bottom border-2" style="border-color: var(--primary-color) !important;">
        <h1 class="display-5 fw-bold font-merriweather text-dark mb-2"><i class="bi bi-headset text-danger me-3"></i>Get in Touch</h1>
        <p class="fs-6 text-muted mb-0">Have a story? Want to advertise? Or just want to say hi? We'd love to hear from you.</p>
    </div>

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="bg-white p-5 rounded shadow-sm border">
                <h4 class="fw-bold mb-4 font-merriweather"><i class="bi bi-envelope-paper text-danger me-2"></i>Send us a Message</h4>
                <form id="contactForm">
                    <?= Security::csrfField() ?>
                    <div class="alert form-alert" style="display:none;"></div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Your Name *</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Email Address *</label>
                            <input type="email" name="email" class="form-control form-control-lg bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Phone Number</label>
                            <input type="text" name="phone" class="form-control form-control-lg bg-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Subject *</label>
                            <input type="text" name="subject" class="form-control form-control-lg bg-light" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Your Message *</label>
                            <textarea name="message" class="form-control form-control-lg bg-light" rows="6" required></textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold shadow-sm rounded-pill"><i class="bi bi-send me-2"></i>Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="bg-light p-4 rounded shadow-sm border mb-4">
                <h4 class="fw-bold mb-4 font-merriweather">Contact Info</h4>
                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                    <li class="d-flex align-items-center">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 45px; height: 45px;">
                            <i class="bi bi-envelope text-danger fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold text-uppercase">Email Us</small>
                            <span class="fw-semibold"><?= e(setting('site_email', 'contact@newsmyjob.com')) ?></span>
                        </div>
                    </li>
                    <li class="d-flex align-items-center">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 45px; height: 45px;">
                            <i class="bi bi-telephone text-danger fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold text-uppercase">Call Us</small>
                            <span class="fw-semibold"><?= e(setting('site_phone', '+1 234 567 8900')) ?></span>
                        </div>
                    </li>
                    <li class="d-flex align-items-center">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 45px; height: 45px;">
                            <i class="bi bi-geo-alt text-danger fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold text-uppercase">Our Office</small>
                            <span class="fw-semibold"><?= e(setting('site_address', '123 News Avenue, Media City')) ?></span>
                        </div>
                    </li>
                </ul>
            </div>
            
            <?php require VIEWS_PATH . '/frontend/includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
