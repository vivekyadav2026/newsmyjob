<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (Auth::check()) {
    redirect(adminUrl('dashboard.php'));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    if (!Security::rateLimit('forgot_password', 3, 15)) {
        $error = 'Too many requests. Please try again later.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $error = 'Email is required.';
        } elseif ($emailError = validateEmail($email)) {
            $error = $emailError;
        } else {
            $userModel = new UserModel();
            $user = $userModel->findByEmail($email);

            if ($user && $user['status'] === 'active') {
                $token = Security::randomToken();
                $userModel->setResetToken((int) $user['id'], $token);
                $resetLink = adminUrl('reset-password.php?token=' . urlencode($token));
                $message = 'If an account exists for that email, a reset link has been generated. Use this link to reset your password: ' . $resetLink;
            } else {
                $message = 'If an account exists for that email, reset instructions have been sent.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= e(setting('site_name', 'NewsMyJob')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card bg-white p-4 p-md-5">
        <div class="text-center mb-4">
            <i class="bi bi-key text-danger" style="font-size: 48px;"></i>
            <h3 class="mt-2 fw-bold">Forgot Password</h3>
            <p class="text-muted">Enter your email to receive a reset link</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <button type="submit" class="btn btn-danger w-100 mb-3">Send Reset Link</button>
            <div class="text-center">
                <a href="<?= adminUrl('login.php') ?>" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
