<?php
/**
 * Admin Header Include
 */
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= e(getSetting('site_name', 'NewsMyJob')) ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>?v=<?= time() ?>" rel="stylesheet">
    <script>const BASE_URL = '<?= BASE_URL ?>'; const CSRF_TOKEN = '<?= csrfToken() ?>';</script>
</head>
<body>
<div class="admin-wrapper">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <?php require __DIR__ . '/navbar.php'; ?>
        <main class="admin-main p-4">
