<?php
/**
 * Admin Header Include
 */
if (!defined('APP_ROOT')) {
    require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
}
$pageTitle = $pageTitle ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> - <?= e(setting('site_name', 'NewsMyJob')) ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
    <script>const BASE_URL = '<?= BASE_URL ?>'; const ADMIN_URL = '<?= ADMIN_URL ?>'; const CSRF_TOKEN = '<?= Security::generateCsrfToken() ?>';</script>
</head>
<body>
<div class="admin-wrapper d-flex">
