<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('news');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(adminUrl('news/index.php'));
}

requireCsrf();

$id = (int) ($_POST['id'] ?? 0);
$newsModel = new NewsModel();
$news = $newsModel->findById($id);

if (!$news) {
    Session::flash('error', 'News article not found.');
    redirect(adminUrl('news/index.php'));
}

if (Auth::role() === 'author' && (int) $news['author_id'] !== Auth::id()) {
    Session::flash('error', 'You cannot delete this article.');
    redirect(adminUrl('news/index.php'));
}

if ($news['featured_image']) {
    deleteUploadedFile($news['featured_image']);
}
foreach ($newsModel->getImages($id) as $img) {
    deleteUploadedFile($img['image_path']);
}

$newsModel->delete($id);
logActivity('delete', 'news', $id, 'Deleted: ' . $news['title']);
Session::flash('success', 'News article deleted successfully.');
redirect(adminUrl('news/index.php'));
