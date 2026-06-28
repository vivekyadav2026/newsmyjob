<?php
/**
 * Authentication & Authorization Functions
 */

declare(strict_types=1);

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user data
 */
function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND status = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

/**
 * Get current user role
 */
function currentUserRole(): ?string
{
    return currentUser()['role'] ?? null;
}

/**
 * Check if user has permission
 */
function hasPermission(string $permission): bool
{
    $role = currentUserRole();
    if (!$role) {
        return false;
    }

    if ($role === 'super_admin') {
        return true;
    }

    static $permissions = [];
    if (!isset($permissions[$role])) {
        $stmt = db()->prepare("SELECT permission_key FROM permissions WHERE role = ?");
        $stmt->execute([$role]);
        $permissions[$role] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return in_array($permission, $permissions[$role], true);
}

/**
 * Require login - redirect if not authenticated
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('error', 'Please login to continue.');
        redirect(adminUrl('login.php'));
    }
}

/**
 * Require permission - redirect if not authorized
 */
function requirePermission(string $permission): void
{
    requireLogin();
    if (!hasPermission($permission)) {
        setFlash('error', 'You do not have permission to access this page.');
        redirect(adminUrl('dashboard.php'));
    }
}

/**
 * Login user
 */
function loginUser(array $user, bool $remember = false): void
{
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];

    $stmt = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);

    if ($remember) {
        $token = generateToken(32);
        $expires = date('Y-m-d H:i:s', strtotime('+' . REMEMBER_ME_DAYS . ' days'));

        $stmt = db()->prepare("UPDATE users SET remember_token = ?, token_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);

        setcookie('remember_token', $token, [
            'expires'  => strtotime($expires),
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    logLogin($user['id'], $user['email'], 'success');
    logActivity('login', 'auth', $user['id'], 'User logged in');
}

/**
 * Logout user
 */
function logoutUser(): void
{
    if (isLoggedIn()) {
        logActivity('logout', 'auth', $_SESSION['user_id'], 'User logged out');

        $stmt = db()->prepare("UPDATE users SET remember_token = NULL, token_expires = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }

    setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/**
 * Check remember me cookie
 */
function checkRememberMe(): void
{
    if (isLoggedIn() || empty($_COOKIE['remember_token'])) {
        return;
    }

    $token = $_COOKIE['remember_token'];
    $stmt = db()->prepare("SELECT * FROM users WHERE remember_token = ? AND token_expires > NOW() AND status = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        loginUser($user, true);
    }
}

/**
 * Log login attempt
 */
function logLogin(?int $userId, string $email, string $status): void
{
    $stmt = db()->prepare(
        "INSERT INTO login_logs (user_id, email, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $userId,
        $email,
        getClientIp(),
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $status
    ]);
}

/**
 * Authenticate user credentials
 */
function authenticate(string $email, string $password): ?array
{
    $stmt = db()->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND status = 1");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    if ($user && verifyPassword($password, $user['password'])) {
        return $user;
    }

    logLogin(null, $email, 'failed');
    return null;
}

/**
 * Create password reset token
 */
function createPasswordResetToken(string $email): ?string
{
    $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND status = 1");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        return null;
    }

    $token = generateToken(32);
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = db()->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires]);

    return $token;
}

/**
 * Verify password reset token
 */
function verifyPasswordResetToken(string $token): ?string
{
    $stmt = db()->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->execute([$token]);
    $result = $stmt->fetch();
    return $result['email'] ?? null;
}

/**
 * Reset password with token
 */
function resetPassword(string $token, string $newPassword): bool
{
    $email = verifyPasswordResetToken($token);
    if (!$email) {
        return false;
    }

    $hash = hashPassword($newPassword);

    $stmt = db()->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);

    $stmt = db()->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->execute([$token]);

    return true;
}

/**
 * Change user password
 */
function changePassword(int $userId, string $currentPassword, string $newPassword): bool
{
    $stmt = db()->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !verifyPassword($currentPassword, $user['password'])) {
        return false;
    }

    $hash = hashPassword($newPassword);
    $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hash, $userId]);
}
