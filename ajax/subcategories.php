<?php
/**
 * AJAX Get Subcategories by Category
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

$categoryId = (int) ($_GET['category_id'] ?? $_POST['category_id'] ?? 0);

if (!$categoryId) {
    jsonResponse(['success' => false, 'message' => 'Category ID required.'], 422);
}

$categoryModel = new CategoryModel();
$category = $categoryModel->findById($categoryId);
if (!$category) {
    jsonResponse(['success' => false, 'message' => 'Category not found.'], 404);
}

$subCategoryModel = new SubCategoryModel();
$subcategories = $subCategoryModel->getByCategory($categoryId);

$items = array_map(static function (array $sub): array {
    return [
        'id'   => (int) $sub['id'],
        'name' => $sub['name'],
        'slug' => $sub['slug'],
    ];
}, $subcategories);

jsonResponse([
    'success'       => true,
    'category_id'   => $categoryId,
    'category_name' => $category['name'],
    'subcategories' => $items,
]);
