<?php
/**
 * AJAX Toggle Bookmark
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

requireCsrf();

if (!Auth::check()) {
    jsonResponse(['success' => false, 'message' => 'Please login to save articles.', 'login_required' => true], 401);
}

$newsId = (int) ($_POST['news_id'] ?? 0);
if (!$newsId) {
    jsonResponse(['success' => false, 'message' => 'Invalid news ID.'], 422);
}

$newsModel = new NewsModel();
$news = $newsModel->findById($newsId);
if (!$news || $news['status'] !== 'published') {
    jsonResponse(['success' => false, 'message' => 'Article not found.'], 404);
}

$sessionId = session_id();
$userId = Auth::id();

try {
    $model = new BookmarkModel();
    $bookmarked = $model->toggle($sessionId, $newsId, $userId);

    jsonResponse([
        'success'    => true,
        'bookmarked' => $bookmarked,
        'message'    => $bookmarked ? 'Article saved to bookmarks.' : 'Bookmark removed.',
    ]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Bookmark action failed.'], 500);
}
