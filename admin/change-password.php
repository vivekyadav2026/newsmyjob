<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

Auth::requireAuth();

$pageTitle = 'Change Password';
$currentPage = 'profile';
$userModel = new UserModel();
$userId = Auth::id();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $current = $_POST['current_password'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';
    $user = $userModel->findById($userId);

    if (!$user || !Security::verifyPassword($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($pwdError = validatePassword($password)) {
        $error = $pwdError;
    } elseif ($password !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $userModel->update($userId, ['password' => $password]);
        logActivity('update', 'profile', $userId, 'Changed password');
        Session::flash('success', 'Password changed successfully.');
        redirect(adminUrl('change-password.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Change Password</h1>
</div>

<?= renderFlash() ?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="card shadow-sm" style="max-width: 500px;">
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-danger">Update Password</button>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
