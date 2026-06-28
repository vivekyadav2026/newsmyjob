<?php
/**
 * Admin - Users CRUD
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('users');

$pageTitle = 'Users';
$userModel = new UserModel();
$editItem = null;
$roles = ['super_admin', 'admin', 'editor', 'author'];
$currentRole = Auth::role();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/users.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $item = $userModel->findById($id);
        if ($item) {
            if ($item['role'] === 'super_admin') {
                Session::flash('error', 'Cannot delete super admin account.');
            } elseif ($id === Auth::id()) {
                Session::flash('error', 'You cannot delete your own account.');
            } else {
                if ($item['avatar']) {
                    deleteUploadedFile($item['avatar']);
                }
                $userModel->delete($id);
                ActivityLogModel::log(Auth::id(), 'delete', 'users', 'Deleted user: ' . $item['name'], $id);
                Session::flash('success', 'User deleted.');
            }
        }
        redirect(ADMIN_URL . '/users.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'author';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($username) || empty($email)) {
        Session::flash('error', 'Name, username and email are required.');
        redirect(ADMIN_URL . '/users.php' . ($id ? '?edit=' . $id : ''));
    }

    if (!in_array($role, $roles, true)) {
        $role = 'author';
    }

    if ($role === 'super_admin' && $currentRole !== 'super_admin') {
        Session::flash('error', 'Only super admin can assign super admin role.');
        redirect(ADMIN_URL . '/users.php' . ($id ? '?edit=' . $id : ''));
    }

    if ($id) {
        $existing = $userModel->findById($id);
        if ($existing && $existing['role'] === 'super_admin' && $currentRole !== 'super_admin') {
            Session::flash('error', 'You cannot edit super admin account.');
            redirect(ADMIN_URL . '/users.php');
        }
    }

    $data = [
        'name'     => Security::sanitize($name),
        'username' => Security::sanitize($username),
        'email'    => Security::sanitize($email),
        'role'     => $role,
        'bio'      => Security::sanitize($_POST['bio'] ?? ''),
        'phone'    => Security::sanitize($_POST['phone'] ?? ''),
        'status'   => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];

    if ($id) {
        $existing = $userModel->findById($id);
        $data['avatar'] = $existing['avatar'] ?? null;

        if (!empty($_FILES['avatar']['name'])) {
            $upload = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
            if ($upload['success']) {
                if ($existing['avatar']) {
                    deleteUploadedFile($existing['avatar']);
                }
                $data['avatar'] = $upload['path'];
            }
        }

        if (!empty($password)) {
            if ($pwdError = validatePassword($password)) {
                Session::flash('error', $pwdError);
                redirect(ADMIN_URL . '/users.php?edit=' . $id);
            }
            $data['password'] = $password;
        }

        $userModel->update($id, $data);
        ActivityLogModel::log(Auth::id(), 'update', 'users', 'Updated user: ' . $data['name'], $id);
        Session::flash('success', 'User updated.');
    } else {
        if (empty($password)) {
            Session::flash('error', 'Password is required for new users.');
            redirect(ADMIN_URL . '/users.php');
        }
        if ($pwdError = validatePassword($password)) {
            Session::flash('error', $pwdError);
            redirect(ADMIN_URL . '/users.php');
        }
        if ($userModel->findByEmail($email)) {
            Session::flash('error', 'Email already exists.');
            redirect(ADMIN_URL . '/users.php');
        }

        $data['password'] = $password;

        if (!empty($_FILES['avatar']['name'])) {
            $upload = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
            if ($upload['success']) {
                $data['avatar'] = $upload['path'];
            }
        }

        $newId = $userModel->create($data);
        ActivityLogModel::log(Auth::id(), 'create', 'users', 'Created user: ' . $data['name'], $newId);
        Session::flash('success', 'User created.');
    }
    redirect(ADMIN_URL . '/users.php');
}

if (!empty($_GET['edit'])) {
    $editItem = $userModel->findById((int) $_GET['edit']);
}

$roleFilter = $_GET['role'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $userModel->getAll($page, ADMIN_PER_PAGE, $roleFilter ?: null);
$users = $result['data'];
$total = $result['total'];

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">Users</h4>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="mb-3"><?= $editItem ? 'Edit User' : 'Add User' ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <?= Security::csrfField() ?>
                        <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($editItem['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= e($editItem['username'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e($editItem['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <?= $editItem ? '(leave blank to keep)' : '<span class="text-danger">*</span>' ?></label>
                            <input type="password" name="password" class="form-control" <?= $editItem ? '' : 'required' ?> minlength="<?= PASSWORD_MIN_LENGTH ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <?php foreach ($roles as $r): ?>
                                <?php if ($r === 'super_admin' && $currentRole !== 'super_admin') continue; ?>
                                <option value="<?= $r ?>" <?= ($editItem['role'] ?? 'author') === $r ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $r)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($editItem['phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="2"><?= e($editItem['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Avatar</label>
                            <?php if (!empty($editItem['avatar'])): ?>
                            <div class="mb-2"><img src="<?= uploadUrl($editItem['avatar']) ?>" alt="" class="rounded-circle" style="width:50px;height:50px;object-fit:cover;"></div>
                            <?php endif; ?>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($editItem['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($editItem['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger"><?= $editItem ? 'Update' : 'Create' ?></button>
                            <?php if ($editItem): ?>
                            <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-outline-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="content-card mb-3">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <?php foreach ($roles as $r): ?>
                                <option value="<?= $r ?>" <?= $roleFilter === $r ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $r)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
                    </form>
                </div>
                <div class="content-card">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr><th>ID</th><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle bg-primary text-white" style="width:32px;height:32px;font-size:12px;"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                                            <?= e($u['name']) ?>
                                        </div>
                                    </td>
                                    <td><?= e($u['email']) ?></td>
                                    <td><span class="badge bg-info"><?= ucwords(str_replace('_', ' ', $u['role'])) ?></span></td>
                                    <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($u['status']) ?></span></td>
                                    <td><?= $u['last_login'] ? formatDateTime($u['last_login']) : '-' ?></td>
                                    <td>
                                        <a href="<?= ADMIN_URL ?>/users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <?php if ($u['role'] !== 'super_admin' && $u['id'] !== Auth::id()): ?>
                                        <form method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?= renderPagination($total, $page, ADMIN_PER_PAGE, ADMIN_URL . '/users.php?' . http_build_query(array_filter(['role' => $roleFilter]))) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
