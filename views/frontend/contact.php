<?php
/**
 * Contact Us Page - Ultra Premium SaaS UI
 */
$pageTitle = 'Contact Us - ' . setting('site_name');
require VIEWS_PATH . '/frontend/includes/header.php';
?>

<style>
/* CSS Variables */
:root {
    --saas-primary: #FF3B30;
    --saas-primary-gradient: linear-gradient(135deg, #FF3B30, #FF0050);
    --saas-secondary: #111827;
    --saas-bg: #F8FAFC;
    --saas-card: #FFFFFF;
    --saas-border: #E5E7EB;
    --saas-text: #111827;
    --saas-muted: #6B7280;
    --saas-radius: 20px;
    --saas-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
    --saas-transition: all 250ms cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background-color: var(--saas-bg);
}

/* Animations */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-up {
    animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.delay-100 { animation-delay: 100ms; opacity: 0; }
.delay-200 { animation-delay: 200ms; opacity: 0; }

/* Hero Section */
.contact-hero {
    position: relative;
    padding: 80px 0;
    background: radial-gradient(circle at top right, rgba(255,59,48,0.05), transparent 50%),
                radial-gradient(circle at bottom left, rgba(17,24,39,0.03), transparent 50%);
    overflow: hidden;
}
.hero-accent-line {
    width: 60px;
    height: 4px;
    background: var(--saas-primary);
    border-radius: 10px;
    margin-bottom: 24px;
}
.reply-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 59, 48, 0.1);
    color: var(--saas-primary);
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 0.875rem;
    font-weight: 600;
}
.reply-badge i { font-size: 10px; margin-right: 8px; animation: pulse 2s infinite; }

/* Floating Hero Cards */
.hero-floating-card {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.8);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05);
    transition: var(--saas-transition);
    transform: translateY(0);
}
.hero-floating-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 30px 50px -15px rgba(255,59,48,0.15);
}
.hfc-icon {
    width: 48px;
    height: 48px;
    background: var(--saas-card);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--saas-primary);
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Main Form Card */
.contact-form-card {
    background: var(--saas-card);
    border-radius: var(--saas-radius);
    padding: 40px;
    box-shadow: var(--saas-shadow);
    border: 1px solid rgba(255,255,255,0.8);
}
@media (max-width: 767px) {
    .contact-form-card { padding: 24px; }
}

/* Floating Labels & Inputs */
.floating-group {
    position: relative;
    margin-bottom: 24px;
}
.floating-group .form-icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 18px;
    color: var(--saas-muted);
    font-size: 1.1rem;
    transition: var(--saas-transition);
    z-index: 10;
}
.floating-group textarea ~ .form-icon {
    top: 24px;
    transform: none;
}
.saas-input {
    width: 100%;
    height: 58px;
    background: var(--saas-bg);
    border: 1px solid var(--saas-border);
    border-radius: 14px;
    padding: 12px 16px 12px 50px;
    font-size: 1rem;
    font-weight: 500;
    color: var(--saas-text);
    transition: var(--saas-transition);
}
textarea.saas-input {
    height: 180px;
    resize: none;
    padding-top: 20px;
}
.floating-label {
    position: absolute;
    left: 50px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--saas-muted);
    transition: var(--saas-transition);
    pointer-events: none;
    font-size: 1rem;
    background: transparent;
}
textarea ~ .floating-label {
    top: 20px;
    transform: none;
}
/* Focus and Filled states */
.saas-input:focus, .saas-input:not(:placeholder-shown) {
    border-color: var(--saas-primary);
    background: var(--saas-card);
    outline: none;
}
.saas-input:focus {
    box-shadow: 0 0 0 4px rgba(255, 59, 48, 0.15);
}
.saas-input:focus ~ .form-icon, .saas-input:not(:placeholder-shown) ~ .form-icon {
    color: var(--saas-primary);
}
.saas-input:focus ~ .floating-label, .saas-input:not(:placeholder-shown) ~ .floating-label {
    top: -10px;
    left: 16px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--saas-card);
    padding: 0 6px;
    color: var(--saas-primary);
}
.char-counter {
    position: absolute;
    bottom: -20px;
    right: 0;
    font-size: 0.75rem;
    color: var(--saas-muted);
}

/* Submit Button */
.btn-saas {
    width: 100%;
    height: 60px;
    background: var(--saas-primary-gradient);
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 1.125rem;
    font-weight: 700;
    box-shadow: 0 10px 20px -5px rgba(255,59,48,0.4);
    transition: var(--saas-transition);
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-saas:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px -5px rgba(255,59,48,0.5);
    color: #fff;
}
.btn-saas::after {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    width: 300px; height: 300px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    transition: transform 0.6s ease-out;
}
.btn-saas:active::after {
    transform: translate(-50%, -50%) scale(1);
    transition: 0s;
}

/* Right Info Card */
.info-card-saas {
    background: linear-gradient(145deg, #1e293b, #0f172a);
    border-radius: 24px;
    padding: 40px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.5);
    height: 100%;
}
.info-card-saas::before {
    content: '';
    position: absolute;
    top: -50%; left: -50%;
    width: 200%; height: 200%;
    background: radial-gradient(circle at center, rgba(255,255,255,0.03) 0%, transparent 50%);
    pointer-events: none;
}
.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 32px;
    transition: var(--saas-transition);
}
.info-item:hover {
    transform: translateX(5px);
}
.info-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(8px);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--saas-primary);
    margin-right: 16px;
    flex-shrink: 0;
    transition: var(--saas-transition);
    border: 1px solid rgba(255,255,255,0.05);
}
.info-item:hover .info-icon {
    background: var(--saas-primary);
    color: #fff;
    transform: scale(1.05);
}
.info-title {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.5);
    margin-bottom: 4px;
}
.info-desc {
    font-size: 1.125rem;
    font-weight: 500;
    color: #fff;
    text-decoration: none;
    transition: var(--saas-transition);
}
a.info-desc:hover { color: var(--saas-primary); }

/* Map Preview Card */
.map-preview {
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    padding: 16px;
    border: 1px solid rgba(255,255,255,0.1);
    margin-top: 40px;
    display: flex;
    align-items: center;
    transition: var(--saas-transition);
}
.map-preview:hover {
    background: rgba(255,255,255,0.08);
}
.map-icon-box {
    width: 40px; height: 40px;
    background: rgba(255,59,48,0.2);
    color: var(--saas-primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
}

/* Social Circular Buttons */
.social-circle-saas {
    width: 40px; height: 40px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    transition: var(--saas-transition);
}
.social-circle-saas:hover {
    background: var(--saas-primary);
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 10px 20px rgba(255,59,48,0.3);
    color: #fff;
}
</style>

<!-- Hero Section -->
<section class="contact-hero border-bottom border-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 animate-fade-up">
                <div class="hero-accent-line"></div>
                <h1 class="display-3 fw-bold text-dark mb-4" style="letter-spacing: -1px; line-height: 1.1;">Get In Touch</h1>
                <p class="fs-5 text-muted mb-4" style="line-height: 1.7; max-width: 500px;">
                    Whether you have a breaking news tip, advertising inquiry, or need technical support, our team is ready to assist you.
                </p>
                <div class="reply-badge">
                    <i class="bi bi-circle-fill"></i> We usually reply within 24 Hours
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="row g-4 position-relative">
                    <div class="col-sm-6 animate-fade-up delay-100">
                        <div class="hero-floating-card mb-4 mt-5">
                            <div class="hfc-icon mb-3"><i class="bi bi-envelope-paper-fill"></i></div>
                            <h5 class="fw-bold mb-1">Email Support</h5>
                            <p class="text-muted small mb-0">Fast, reliable responses.</p>
                        </div>
                    </div>
                    <div class="col-sm-6 animate-fade-up delay-200">
                        <div class="hero-floating-card">
                            <div class="hfc-icon mb-3"><i class="bi bi-telephone-fill"></i></div>
                            <h5 class="fw-bold mb-1">Phone Support</h5>
                            <p class="text-muted small mb-0">Speak directly with us.</p>
                        </div>
                        <div class="hero-floating-card mt-4">
                            <div class="hfc-icon mb-3"><i class="bi bi-clock-history"></i></div>
                            <h5 class="fw-bold mb-1">Working Hours</h5>
                            <p class="text-muted small mb-0">24/7 News Coverage.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Contact Section -->
<section class="py-5" style="margin-top: -40px; position: relative; z-index: 10;">
    <div class="container">
        <div class="row g-4 g-lg-5">
            
            <!-- Left: Form Card (65%) -->
            <div class="col-lg-7 animate-fade-up">
                <div class="contact-form-card">
                    <h3 class="fw-bold mb-2">Send us a Message</h3>
                    <p class="text-muted mb-5">We'd love to hear from you. Fill out the form below.</p>
                    
                    <form id="contactForm">
                        <?= Security::csrfField() ?>
                        <div class="alert form-alert" style="display:none; border-radius: 12px; padding: 16px;"></div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="floating-group">
                                    <i class="bi bi-person form-icon"></i>
                                    <input type="text" name="name" id="f_name" class="saas-input" placeholder=" " required>
                                    <label for="f_name" class="floating-label">Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="floating-group">
                                    <i class="bi bi-envelope form-icon"></i>
                                    <input type="email" name="email" id="f_email" class="saas-input" placeholder=" " required>
                                    <label for="f_email" class="floating-label">Email</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="floating-group">
                                    <i class="bi bi-telephone form-icon"></i>
                                    <input type="text" name="phone" id="f_phone" class="saas-input" placeholder=" ">
                                    <label for="f_phone" class="floating-label">Phone</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="floating-group">
                                    <i class="bi bi-chat-left-text form-icon"></i>
                                    <input type="text" name="subject" id="f_subject" class="saas-input" placeholder=" " required>
                                    <label for="f_subject" class="floating-label">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="floating-group mb-4">
                                    <i class="bi bi-pencil form-icon"></i>
                                    <textarea name="message" id="f_message" class="saas-input" placeholder=" " required oninput="document.getElementById('charCount').textContent = this.value.length"></textarea>
                                    <label for="f_message" class="floating-label">Message</label>
                                    <div class="char-counter"><span id="charCount">0</span> chars</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-saas">
                                    <i class="bi bi-send-fill me-2"></i> Send Message
                                </button>
                                <div class="text-center mt-4 text-muted small d-flex align-items-center justify-content-center">
                                    <i class="bi bi-shield-lock-fill text-success me-2 fs-6"></i> Your information is 100% secure.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Info Card (35%) -->
            <div class="col-lg-5 animate-fade-up delay-100">
                <div class="info-card-saas">
                    
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <div class="info-title">Email</div>
                            <a href="mailto:<?= e(setting('site_email', 'contact@newsmyjob.com')) ?>" class="info-desc d-block"><?= e(setting('site_email', 'contact@newsmyjob.com')) ?></a>
                            <small class="text-white-50">Drop us a line anytime</small>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <div class="info-title">Phone</div>
                            <a href="tel:<?= e(setting('site_phone', '+1 234 567 8900')) ?>" class="info-desc d-block"><?= e(setting('site_phone', '+1 234 567 8900')) ?></a>
                            <small class="text-white-50">Mon-Fri, 9am - 6pm</small>
                        </div>
                    </div>

                    <div class="info-item mb-0">
                        <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <div class="info-title">Office Address</div>
                            <span class="info-desc d-block"><?= e(setting('site_address', 'Media City, Tech Park')) ?></span>
                            <small class="text-white-50">Global HQ</small>
                        </div>
                    </div>

                    <div class="map-preview">
                        <div class="map-icon-box"><i class="bi bi-map-fill fs-5"></i></div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">Find us on Maps</div>
                            <div class="small text-white-50">Get directions to our office</div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                        <div class="info-title mb-3">Connect With Us</div>
                        <div class="d-flex gap-3">
                            <a href="https://x.com/Toolxy5" class="social-circle-saas"><i class="bi bi-twitter-x"></i></a>
                            <a href="https://www.instagram.com/toolxy.in?igsh=dHIwNnMyajdicnN3" class="social-circle-saas"><i class="bi bi-instagram"></i></a>
                            <a href="https://www.linkedin.com/in/md-anish-35122540b" class="social-circle-saas"><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button[type="submit"]');
            const alert = this.querySelector('.form-alert');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
            btn.disabled = true;
            alert.style.display = 'none';
            
            try {
                const formData = new FormData(this);
                const response = await fetch(BASE_URL + '/api/contact.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                alert.className = 'alert form-alert ' + (data.success ? 'alert-success bg-success bg-opacity-10 text-success border border-success' : 'alert-danger bg-danger bg-opacity-10 text-danger border border-danger');
                alert.innerHTML = data.success ? `<i class="bi bi-check-circle-fill me-2"></i>${data.message}` : `<i class="bi bi-exclamation-circle-fill me-2"></i>${data.message}`;
                alert.style.display = 'block';
                
                if (data.success) {
                    this.reset();
                    document.getElementById('charCount').textContent = '0';
                }
            } catch (error) {
                alert.className = 'alert form-alert alert-danger bg-danger bg-opacity-10 text-danger border border-danger';
                alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>An unexpected error occurred. Please try again.';
                alert.style.display = 'block';
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
});
</script>

<?php require VIEWS_PATH . '/frontend/includes/footer.php'; ?>
