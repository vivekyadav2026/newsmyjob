<?php
/**
 * Authentication & Authorization
 */

declare(strict_types=1);

class Auth
{
    public const ROLES = [
        'super_admin' => 4,
        'admin'       => 3,
        'editor'      => 2,
        'author'      => 1,
    ];

    public const ROLE_PERMISSIONS = [
        'super_admin' => ['*'],
        'admin'       => [
            'dashboard', 'news', 'categories', 'subcategories', 'breaking',
            'featured', 'trending', 'media', 'users', 'settings', 'ads',
            'seo', 'reports', 'comments', 'backup', 'newsletter', 'contacts', 'menus',
        ],
        'editor'      => [
            'dashboard', 'news', 'categories', 'subcategories', 'breaking',
            'featured', 'trending', 'media', 'comments', 'reports',
        ],
        'author'      => ['dashboard', 'news', 'media'],
    ];

    public static function attempt(string $login, string $password, bool $remember = false): bool
    {
        $userModel = new UserModel();
        $user = $userModel->findByLogin($login);

        if (!$user || $user['status'] !== 'active') {
            self::logLoginAttempt(null, $login, false);
            return false;
        }

        if (!Security::verifyPassword($password, $user['password'])) {
            self::logLoginAttempt((int) $user['id'], $login, false);
            return false;
        }

        self::login($user);

        if ($remember) {
            self::setRememberToken((int) $user['id']);
        }

        self::logLoginAttempt((int) $user['id'], $user['email'], true);
        ActivityLogModel::log((int) $user['id'], 'login', 'auth', 'User logged in');
        $userModel->updateLastLogin((int) $user['id']);

        return true;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('user_avatar', $user['avatar'] ?? '');
    }

    public static function logout(): void
    {
        $userId = self::id();
        if ($userId) {
            ActivityLogModel::log($userId, 'logout', 'auth', 'User logged out');
            self::clearRememberToken($userId);
        }
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function attemptRememberLogin(): bool
    {
        if (self::check() || empty($_COOKIE['remember_token'])) {
            return false;
        }

        $token = hash('sha256', $_COOKIE['remember_token']);
        $userModel = new UserModel();
        $user = $userModel->findByRememberToken($token);

        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id ? (int) $id : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'     => self::id(),
            'name'   => Session::get('user_name'),
            'email'  => Session::get('user_email'),
            'role'   => Session::get('user_role'),
            'avatar' => Session::get('user_avatar'),
        ];
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function can(string $permission): bool
    {
        $role = self::role();
        if (!$role) {
            return false;
        }

        $permissions = self::ROLE_PERMISSIONS[$role] ?? [];
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Please login to continue.');
            redirect(ADMIN_URL . '/login.php');
        }
    }

    public static function requirePermission(string $permission): void
    {
        self::requireAuth();
        if (!self::can($permission)) {
            Session::flash('error', 'You do not have permission to access this page.');
            redirect(ADMIN_URL . '/dashboard.php');
        }
    }

    private static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hashed = hash('sha256', $token);
        (new UserModel())->setRememberToken($userId, $hashed);

        setcookie('remember_token', $token, [
            'expires'  => time() + (REMEMBER_ME_DAYS * 86400),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearRememberToken(int $userId): void
    {
        (new UserModel())->clearRememberToken($userId);
        setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
    }

    private static function logLoginAttempt(?int $userId, string $email, bool $success): void
    {
        (new LoginLogModel())->create([
            'user_id'    => $userId,
            'email'      => $email,
            'ip_address' => getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'success'    => $success ? 1 : 0,
        ]);
    }
}
