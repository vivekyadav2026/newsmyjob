<?php
/**
 * AJAX Contact Form
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request.'], 405);
}

if (!Security::verifyCsrf()) {
    jsonResponse(['success' => false, 'message' => 'Invalid security token.'], 403);
}

if (!Security::rateLimit('contact', 3, 30)) {
    jsonResponse(['success' => false, 'message' => 'Too many messages. Please try again later.']);
}

$errors = validate([
    'name'    => ['required'],
    'email'   => ['required', 'email'],
    'subject' => ['required'],
    'message' => ['required'],
], $_POST);

if ($errors) {
    jsonResponse(['success' => false, 'message' => reset($errors)]);
}

$model = new ContactModel();
$model->create([
    'name'    => Security::sanitize($_POST['name']),
    'email'   => Security::sanitize($_POST['email']),
    'phone'   => Security::sanitize($_POST['phone'] ?? ''),
    'subject' => Security::sanitize($_POST['subject']),
    'message' => Security::sanitize($_POST['message']),
]);

jsonResponse(['success' => true, 'message' => 'Your message has been sent successfully!']);
