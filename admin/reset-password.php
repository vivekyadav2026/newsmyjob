<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (Auth::check()) {
    redirect(adminUrl('dashboard.php'));
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$userModel = new UserModel();
$user = $token ? $userModel->findByResetToken($token) : null;
$error = '';
$success = false;

if (!$user) {
    $error = 'Invalid or expired reset token.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    requireCsrf();

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($pwdError = validatePassword($password)) {
        $error = $pwdError;
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $userModel->update((int) $user['id'], ['password' => $password]);
        $userModel->clearResetToken((int) $user['id']);
        Session::flash('success', 'Password reset successfully. Please login.');
        redirect(adminUrl('login.php'));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= e(setting('site_name', 'NewsMyJob')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card bg-white p-4 p-md-5">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock text-danger" style="font-size: 48px;"></i>
            <h3 class="mt-2 fw-bold">Reset Password</h3>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-danger w-100 mb-3">Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="text-center">
            <a href="<?= adminUrl('login.php') ?>" class="text-decoration-none">Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>
