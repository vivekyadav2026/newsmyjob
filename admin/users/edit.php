<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('users');

$id = (int) ($_GET['id'] ?? 0);
$userModel = new UserModel();
$user = $userModel->findById($id);
if (!$user) { Session::flash('error', 'User not found.'); redirect(adminUrl('users/index.php')); }

$pageTitle = 'Edit User';
$currentPage = 'users';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $data = [
        'name'     => trim($_POST['name'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'role'     => $_POST['role'] ?? $user['role'],
        'status'   => $_POST['status'] ?? $user['status'],
        'phone'    => trim($_POST['phone'] ?? ''),
        'bio'      => trim($_POST['bio'] ?? ''),
    ];

    if (empty($data['name'])) $errors[] = 'Name is required.';
    if ($emailErr = validateEmail($data['email'])) $errors[] = $emailErr;
    if (Auth::role() !== 'super_admin' && $data['role'] === 'super_admin') $errors[] = 'Cannot assign super admin role.';
    if ($user['role'] === 'super_admin' && Auth::role() !== 'super_admin') $errors[] = 'Cannot edit super admin.';

    $existing = $userModel->findByEmail($data['email']);
    if ($existing && (int) $existing['id'] !== $id) $errors[] = 'Email already in use.';

    if (!empty($_POST['password'])) {
        if ($pwdErr = validatePassword($_POST['password'])) $errors[] = $pwdErr;
        else $data['password'] = $_POST['password'];
    }

    if (!empty($_FILES['avatar']['name'])) {
        $upload = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
        if ($upload['success']) {
            if ($user['avatar']) deleteUploadedFile($user['avatar']);
            $data['avatar'] = $upload['path'];
        } else $errors[] = $upload['message'];
    }

    if (empty($errors)) {
        $userModel->update($id, $data);
        logActivity('update', 'users', $id, 'Updated user: ' . $data['name']);
        Session::flash('success', 'User updated successfully.');
        redirect(adminUrl('users/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit User</h1>
    <a href="<?= adminUrl('users/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?= renderFlash() ?>
<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>

<div class="card shadow-sm"><div class="card-body">
<form method="POST" enctype="multipart/form-data"><?= csrfField() ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
    <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?= e($user['username']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required></div>
    <div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></div>
    <div class="col-md-4"><label class="form-label">Role</label><select name="role" class="form-select" <?= $user['role'] === 'super_admin' ? 'disabled' : '' ?>><?php foreach (array_keys(Auth::ROLES) as $r): ?><option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $r)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= $user['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $user['status']==='inactive'?'selected':'' ?>>Inactive</option><option value="banned" <?= $user['status']==='banned'?'selected':'' ?>>Banned</option></select></div>
    <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Avatar</label><?php if ($user['avatar']): ?><img src="<?= uploadUrl($user['avatar']) ?>" class="d-block mb-2 rounded-circle" width="60"><?php endif; ?><input type="file" name="avatar" class="form-control" accept="image/*"></div>
    <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"><?= e($user['bio'] ?? '') ?></textarea></div>
</div>
<button type="submit" class="btn btn-danger mt-3">Update User</button>
</form></div></div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
