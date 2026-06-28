<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('breaking');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(adminUrl('breaking/index.php'));
requireCsrf();

$id = (int) ($_POST['id'] ?? 0);
$model = new BreakingNewsModel();
$item = $model->findById($id);
if (!$item) { Session::flash('error', 'Not found.'); redirect(adminUrl('breaking/index.php')); }

$newStatus = $item['status'] === 'active' ? 'inactive' : 'active';
$model->update($id, array_merge($item, ['status' => $newStatus]));
logActivity('toggle', 'breaking', $id, 'Toggled status to ' . $newStatus);
Session::flash('success', 'Status updated to ' . $newStatus . '.');
redirect(adminUrl('breaking/index.php'));
