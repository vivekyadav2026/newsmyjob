<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('users');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(adminUrl('users/index.php'));
requireCsrf();

$id = (int) ($_POST['id'] ?? 0);
$userModel = new UserModel();
$user = $userModel->findById($id);

if (!$user) { Session::flash('error', 'User not found.'); redirect(adminUrl('users/index.php')); }
if ($user['role'] === 'super_admin') { Session::flash('error', 'Cannot delete super admin.'); redirect(adminUrl('users/index.php')); }
if ((int) $id === Auth::id()) { Session::flash('error', 'Cannot delete your own account.'); redirect(adminUrl('users/index.php')); }

$userModel->delete($id);
logActivity('delete', 'users', $id, 'Deleted user: ' . $user['name']);
Session::flash('success', 'User deleted successfully.');
redirect(adminUrl('users/index.php'));
