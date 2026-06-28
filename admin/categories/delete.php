<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('categories');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(adminUrl('categories/index.php'));
requireCsrf();

$id = (int) ($_POST['id'] ?? 0);
$model = new CategoryModel();
$cat = $model->findById($id);
if (!$cat) { Session::flash('error', 'Category not found.'); redirect(adminUrl('categories/index.php')); }

if ($cat['image']) deleteUploadedFile($cat['image']);
$model->delete($id);
logActivity('delete', 'categories', $id, 'Deleted category: ' . $cat['name']);
Session::flash('success', 'Category deleted successfully.');
redirect(adminUrl('categories/index.php'));
