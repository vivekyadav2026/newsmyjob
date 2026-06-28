<?php
/**
 * Security - CSRF, XSS, Input Sanitization
 */

declare(strict_types=1);

class Security
{
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (!Session::has(CSRF_TOKEN_NAME)) {
            Session::set(CSRF_TOKEN_NAME, bin2hex(random_bytes(32)));
        }

        return Session::get(CSRF_TOKEN_NAME);
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $sessionToken = Session::get(CSRF_TOKEN_NAME);
        return hash_equals($sessionToken ?? '', $token);
    }

    /**
     * Output CSRF hidden input field
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e($token) . '">';
    }

    /**
     * Verify CSRF from POST request
     */
    public static function verifyCsrf(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return self::validateCsrfToken($token);
    }

    /**
     * Sanitize string for XSS prevention
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize HTML content (allow safe tags for rich text)
     */
    public static function sanitizeHtml(string $html): string
    {
        $allowed = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><table><tr><td><th><thead><tbody><iframe><div><span><sub><sup>';
        return strip_tags($html, $allowed);
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate random token
     */
    public static function randomToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Rate limit check (simple session-based)
     */
    public static function rateLimit(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool
    {
        $attempts = Session::get('_rate_' . $key, []);
        $now = time();
        $attempts = array_filter($attempts, fn($t) => $t > ($now - ($decayMinutes * 60)));

        if (count($attempts) >= $maxAttempts) {
            return false;
        }

        $attempts[] = $now;
        Session::set('_rate_' . $key, $attempts);
        return true;
    }
}
