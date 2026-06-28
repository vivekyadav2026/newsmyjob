<?php
/**
 * AJAX Comment Submit
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request.'], 405);
}

if (!Security::verifyCsrf()) {
    jsonResponse(['success' => false, 'message' => 'Invalid security token.'], 403);
}

if (setting('enable_comments') !== '1') {
    jsonResponse(['success' => false, 'message' => 'Comments are disabled.']);
}

if (!Security::rateLimit('comment', 5, 15)) {
    jsonResponse(['success' => false, 'message' => 'Too many comments. Please wait.']);
}

$errors = validate([
    'name'    => ['required'],
    'email'   => ['required', 'email'],
    'comment' => ['required'],
], $_POST);

if ($errors) {
    jsonResponse(['success' => false, 'message' => reset($errors)]);
}

$newsId = (int) ($_POST['news_id'] ?? 0);
$newsModel = new NewsModel();
if (!$newsModel->findById($newsId)) {
    jsonResponse(['success' => false, 'message' => 'Invalid article.']);
}

$model = new CommentModel();
$model->create([
    'news_id' => $newsId,
    'name'    => Security::sanitize($_POST['name']),
    'email'   => Security::sanitize($_POST['email']),
    'comment' => Security::sanitize($_POST['comment']),
    'status'  => 'pending',
]);

jsonResponse(['success' => true, 'message' => 'Your comment has been submitted and is awaiting moderation.']);
