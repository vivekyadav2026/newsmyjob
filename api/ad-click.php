<?php
/**
 * API - Ad Click Tracking
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$model = new AdvertisementModel();
$ad = $model->findById($id);

if ($ad) {
    $model->incrementClick($id);
    $redirect = $ad['link'] ?? BASE_URL;
    redirect($redirect);
}

redirect(BASE_URL);
