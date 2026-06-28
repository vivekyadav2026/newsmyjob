<?php
/**
 * Admin Login Page
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (Auth::check()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        $errors[] = 'Invalid security token. Please try again.';
    } elseif (!Security::rateLimit('login', 5, 15)) {
        $errors[] = 'Too many login attempts. Please try again later.';
    } else {
        $email = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $errors[] = 'Email and password are required.';
        } elseif (Auth::attempt($email, $password, $remember)) {
            redirect(ADMIN_URL . '/dashboard.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= e(setting('site_name', 'NewsMyJob')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card bg-white p-4 p-md-5">
        <div class="text-center mb-4">
            <i class="bi bi-newspaper text-danger" style="font-size: 48px;"></i>
            <h3 class="mt-2 fw-bold"><?= e(setting('site_name', 'NewsMyJob')) ?></h3>
            <p class="text-muted">Admin Panel Login</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger"><?= e($errors[0]) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= Security::csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>
                <a href="forgot-password.php" class="text-decoration-none small">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </form>
        <p class="text-center text-muted small mt-4 mb-0">Default: admin@newsmyjob.com / Admin@123</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
