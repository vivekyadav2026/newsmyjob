<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('subcategories');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(adminUrl('subcategories/index.php'));
requireCsrf();

$id = (int) ($_POST['id'] ?? 0);
$model = new SubCategoryModel();
$item = $model->findById($id);
if (!$item) { Session::flash('error', 'Not found.'); redirect(adminUrl('subcategories/index.php')); }

$model->delete($id);
logActivity('delete', 'subcategories', $id, 'Deleted: ' . $item['name']);
Session::flash('success', 'Sub category deleted.');
redirect(adminUrl('subcategories/index.php'));
