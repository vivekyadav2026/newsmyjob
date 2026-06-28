<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('ads');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(adminUrl('ads/index.php'));
requireCsrf();

$id = (int) ($_POST['id'] ?? 0);
$model = new AdvertisementModel();
$ad = $model->findById($id);
if (!$ad) { Session::flash('error', 'Not found.'); redirect(adminUrl('ads/index.php')); }

if ($ad['image']) deleteUploadedFile($ad['image']);
$model->delete($id);
logActivity('delete', 'ads', $id, 'Deleted ad');
Session::flash('success', 'Advertisement deleted.');
redirect(adminUrl('ads/index.php'));
