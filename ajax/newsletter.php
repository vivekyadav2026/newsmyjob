<?php
/**
 * AJAX Newsletter Subscribe
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request.'], 405);
}

if (!Security::verifyCsrf()) {
    jsonResponse(['success' => false, 'message' => 'Invalid security token.'], 403);
}

$email = Security::sanitize($_POST['email'] ?? '');
$name = Security::sanitize($_POST['name'] ?? '');

if ($error = validateEmail($email)) {
    jsonResponse(['success' => false, 'message' => $error]);
}

if (setting('enable_newsletter') !== '1') {
    jsonResponse(['success' => false, 'message' => 'Newsletter is currently disabled.']);
}

$model = new NewsletterModel();
if ($model->subscribe($email, $name ?: null)) {
    jsonResponse(['success' => true, 'message' => 'Thank you for subscribing!']);
}

jsonResponse(['success' => false, 'message' => 'Subscription failed. Please try again.']);
