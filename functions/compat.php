<?php
declare(strict_types=1);

function adminUrl(string $path = ''): string
{
    return rtrim(ADMIN_URL, '/') . '/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function uploadUrl(string $path): string
{
    if ($path === '') {
        return asset('images/placeholder.svg');
    }
    if (str_starts_with($path, 'http')) {
        return $path;
    }
    return UPLOADS_URL . '/' . ltrim($path, '/');
}

function csrfField(): string
{
    return Security::csrfField();
}

function csrfToken(): string
{
    return Security::generateCsrfToken();
}

function requireCsrf(): void
{
    if (!Security::verifyCsrf()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        Session::flash('error', 'Invalid security token.');
        redirect($_SERVER['HTTP_REFERER'] ?? adminUrl('dashboard.php'));
    }
}

function isLoggedIn(): bool
{
    return Auth::check();
}

function currentUser(): ?array
{
    return Auth::user();
}

function hasPermission(string $permission): bool
{
    return Auth::can($permission);
}

function requireLogin(): void
{
    Auth::requireAuth();
}

function requirePermission(string $permission): void
{
    Auth::requirePermission($permission);
}

function getSetting(string $key, mixed $default = ''): mixed
{
    return setting($key, $default);
}

function setFlash(string $type, string $message): void
{
    Session::flash($type, $message);
}

function getFlash(): ?array
{
    $types = ['success', 'error', 'warning', 'info'];
    foreach ($types as $type) {
        $message = Session::getFlash($type);
        if ($message !== null) {
            return ['type' => $type, 'message' => $message];
        }
    }
    return null;
}

function renderFlash(): string
{
    $flash = getFlash();
    if (!$flash) {
        return '';
    }
    $type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
        . e((string) $flash['message'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

function logActivity(string $action, string $module, ?int $recordId = null, ?string $description = null): void
{
    ActivityLogModel::log(Auth::id(), $action, $module, $description, $recordId);
}

function slugify(string $text): string
{
    return generateSlug($text);
}

function uniqueSlug(PDO $pdo, string $table, string $slug, ?int $excludeId = null): string
{
    $original = $slug;
    $counter = 1;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$slug];
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $original . '-' . $counter++;
    }
}

function cleanHtml(string $html): string
{
    return Security::sanitizeHtml($html);
}

function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function pagination(int $total, int $perPage, int $currentPage, string $baseUrl): string
{
    return renderPagination($total, $currentPage, $perPage, $baseUrl);
}

if (!function_exists('getYouTubeId')) {
    function getYouTubeId(string $url): ?string
    {
        return getYoutubeId($url);
    }
}

function getYouTubeEmbed(string $url): ?string
{
    $id = getYoutubeId($url);
    return $id ? "https://www.youtube.com/embed/{$id}" : null;
}

function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

function view(string $path, array $data = []): void
{
    extract($data);
    $file = VIEWS_PATH . '/' . ltrim($path, '/') . '.php';
    if (!file_exists($file)) {
        throw new RuntimeException("View not found: {$path}");
    }
    require $file;
}

function updateSetting(string $key, ?string $value, string $group = 'general'): bool
{
    return (new SettingsModel())->set($key, $value, $group);
}

function trackVisitor(?string $pageUrl = null): void
{
    try {
        (new ReportModel())->trackVisit(getClientIp(), null, 'page');
    } catch (Exception $e) {
    }
}

function checkRateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    return Security::rateLimit($key, $maxAttempts, (int) ceil($windowSeconds / 60));
}
