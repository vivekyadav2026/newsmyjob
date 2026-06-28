<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('breaking');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(adminUrl('breaking/index.php'));
requireCsrf();

$id = (int) ($_POST['id'] ?? 0);
$model = new BreakingNewsModel();
$item = $model->findById($id);
if (!$item) { Session::flash('error', 'Not found.'); redirect(adminUrl('breaking/index.php')); }

$model->delete($id);
logActivity('delete', 'breaking', $id, 'Deleted breaking news');
Session::flash('success', 'Breaking news deleted.');
redirect(adminUrl('breaking/index.php'));
