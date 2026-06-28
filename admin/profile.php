<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

Auth::requireAuth();

$pageTitle = 'My Profile';
$currentPage = 'profile';
$userModel = new UserModel();
$userId = Auth::id();
$user = $userModel->findById($userId);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $data = [
        'name'  => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'bio'   => trim($_POST['bio'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
    ];

    if (empty($data['name'])) {
        $error = 'Name is required.';
    } elseif ($emailError = validateEmail($data['email'])) {
        $error = $emailError;
    } else {
        $existing = $userModel->findByEmail($data['email']);
        if ($existing && (int) $existing['id'] !== $userId) {
            $error = 'Email is already in use.';
        }
    }

    if (!empty($_FILES['avatar']['name'])) {
        $upload = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            if (!empty($user['avatar'])) {
                deleteUploadedFile($user['avatar']);
            }
            $data['avatar'] = $upload['path'];
        } else {
            $error = $upload['message'];
        }
    }

    if (!$error) {
        $userModel->update($userId, $data);
        Session::set('user_name', $data['name']);
        Session::set('user_email', $data['email']);
        if (isset($data['avatar'])) {
            Session::set('user_avatar', $data['avatar']);
        }
        logActivity('update', 'profile', $userId, 'Updated profile');
        Session::flash('success', 'Profile updated successfully.');
        redirect(adminUrl('profile.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Profile</h1>
    <a href="<?= adminUrl('change-password.php') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-key"></i> Change Password</a>
</div>

<?= renderFlash() ?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row">
                <div class="col-md-3 text-center mb-3">
                    <img src="<?= uploadUrl($user['avatar'] ?? '') ?>" alt="Avatar" class="rounded-circle mb-2" width="120" height="120" style="object-fit:cover;">
                    <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="3"><?= e($user['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?= e(ucfirst(str_replace('_', ' ', $user['role']))) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Login</label>
                            <input type="text" class="form-control" value="<?= e(formatDateTime($user['last_login'] ?? '')) ?>" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-danger">Save Profile</button>
            </div>
        </form>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
