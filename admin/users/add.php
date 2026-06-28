<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('users');

$pageTitle = 'Add User';
$currentPage = 'users';
$userModel = new UserModel();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $data = [
        'name'     => trim($_POST['name'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'role'     => $_POST['role'] ?? 'author',
        'status'   => $_POST['status'] ?? 'active',
        'phone'    => trim($_POST['phone'] ?? ''),
        'bio'      => trim($_POST['bio'] ?? ''),
    ];

    if (empty($data['name'])) $errors[] = 'Name is required.';
    if (empty($data['username'])) $errors[] = 'Username is required.';
    if ($emailErr = validateEmail($data['email'])) $errors[] = $emailErr;
    if ($pwdErr = validatePassword($data['password'])) $errors[] = $pwdErr;
    if (!in_array($data['role'], array_keys(Auth::ROLES), true)) $errors[] = 'Invalid role.';
    if (Auth::role() !== 'super_admin' && $data['role'] === 'super_admin') $errors[] = 'Cannot create super admin.';
    if ($userModel->findByEmail($data['email'])) $errors[] = 'Email already exists.';
    if ($userModel->findByLogin($data['username'])) $errors[] = 'Username already exists.';

    if (empty($errors)) {
        $id = $userModel->create($data);
        logActivity('create', 'users', $id, 'Created user: ' . $data['name']);
        Session::flash('success', 'User created successfully.');
        redirect(adminUrl('users/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add User</h1>
    <a href="<?= adminUrl('users/index.php') ?>" class="btn btn-outline-secondary">Back</a>
</div>

<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card shadow-sm"><div class="card-body">
<form method="POST"><?= csrfField() ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required minlength="<?= PASSWORD_MIN_LENGTH ?>"></div>
    <div class="col-md-4"><label class="form-label">Role</label><select name="role" class="form-select"><?php foreach (array_keys(Auth::ROLES) as $r): if ($r === 'super_admin' && Auth::role() !== 'super_admin') continue; ?><option value="<?= $r ?>"><?= ucfirst(str_replace('_', ' ', $r)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option><option value="banned">Banned</option></select></div>
    <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
    <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"></textarea></div>
</div>
<button type="submit" class="btn btn-danger mt-3">Create User</button>
</form></div></div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
