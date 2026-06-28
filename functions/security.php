<?php
/**
 * Security Functions - CSRF, XSS, Validation
 */

declare(strict_types=1);

/**
 * Initialize secure session
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
}

/**
 * Generate CSRF token field
 */
function csrfField(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e($_SESSION[CSRF_TOKEN_NAME] ?? '') . '">';
}

/**
 * Get CSRF token value
 */
function csrfToken(): string
{
    return $_SESSION[CSRF_TOKEN_NAME] ?? '';
}

/**
 * Verify CSRF token
 */
function verifyCsrf(?string $token = null): bool
{
    $token = $token ?? ($_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $sessionToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';

    if (empty($token) || empty($sessionToken)) {
        return false;
    }

    return hash_equals($sessionToken, $token);
}

/**
 * Require valid CSRF or die
 */
function requireCsrf(): void
{
    if (!verifyCsrf()) {
        if (isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        setFlash('error', 'Invalid security token. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? url());
    }
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize array of inputs
 */
function sanitizeArray(array $data): array
{
    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitizeArray($value);
        } else {
            $sanitized[$key] = is_string($value) ? sanitize($value) : $value;
        }
    }
    return $sanitized;
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isValidPassword(string $password): bool
{
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

/**
 * Hash password
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Validate required fields
 */
function validateRequired(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    return $errors;
}

/**
 * Clean HTML content (allow safe tags for CKEditor)
 */
function cleanHtml(string $html): string
{
    $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><table><thead><tbody><tr><th><td><figure><figcaption><iframe><div><span><hr>';
    return strip_tags($html, $allowedTags);
}

/**
 * Generate random token
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Rate limit check (simple session-based)
 */
function checkRateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $now = time();
    $rateKey = 'rate_limit_' . $key;

    if (!isset($_SESSION[$rateKey])) {
        $_SESSION[$rateKey] = ['count' => 0, 'start' => $now];
    }

    if ($now - $_SESSION[$rateKey]['start'] > $windowSeconds) {
        $_SESSION[$rateKey] = ['count' => 0, 'start' => $now];
    }

    $_SESSION[$rateKey]['count']++;

    return $_SESSION[$rateKey]['count'] <= $maxAttempts;
}
