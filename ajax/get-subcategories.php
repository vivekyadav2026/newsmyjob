<?php
/**
 * AJAX Get Sub Categories by Category
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

$categoryId = (int) ($_GET['category_id'] ?? 0);
$model = new SubCategoryModel();
$subCategories = $model->getByCategory($categoryId);

jsonResponse(['subcategories' => $subCategories]);
