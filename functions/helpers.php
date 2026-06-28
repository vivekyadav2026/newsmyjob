<?php
/**
 * Helper Functions
 */

declare(strict_types=1);

/**
 * Escape output for HTML
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to URL
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Get client IP address
 */
function getClientIp(): string
{
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

/**
 * Generate SEO-friendly slug
 */
function generateSlug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Format date for display
 */
function formatDate(?string $date, string $format = 'M d, Y'): string
{
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime(?string $datetime, string $format = 'M d, Y h:i A'): string
{
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Calculate read time from content
 */
function calculateReadTime(string $content): int
{
    $wordCount = str_word_count(strip_tags($content));
    return max(1, (int) ceil($wordCount / 200));
}

/**
 * Get setting value
 */
function setting(string $key, mixed $default = ''): mixed
{
    static $settings = null;
    if ($settings === null) {
        $model = new SettingsModel();
        $settings = $model->getAllAsArray();
    }
    return $settings[$key] ?? $default;
}

/**
 * Build news URL
 */
function newsUrl(string $slug): string
{
    return BASE_URL . '/news/' . $slug;
}

/**
 * Build category URL
 */
function categoryUrl(string $slug): string
{
    return BASE_URL . '/category/' . $slug;
}

/**
 * Upload file helper
 */
function uploadFile(array $file, string $directory, array $allowedTypes, int $maxSize): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload failed.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes, true)) {
        return ['success' => false, 'message' => 'Invalid file type.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('media_', true) . '.' . strtolower($ext);
    $uploadDir = UPLOADS_PATH . '/' . $directory;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filepath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to save file.'];
    }

    return [
        'success'  => true,
        'filename' => $filename,
        'path'     => $directory . '/' . $filename,
        'url'      => UPLOADS_URL . '/' . $directory . '/' . $filename,
        'mime'     => $mimeType,
        'size'     => $file['size'],
    ];
}

/**
 * Delete uploaded file
 */
function deleteUploadedFile(string $path): bool
{
    $fullPath = UPLOADS_PATH . '/' . ltrim($path, '/');
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * JSON response helper
 */
function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get pagination offset
 */
function getOffset(int $page, int $perPage = FRONTEND_PER_PAGE): int
{
    return max(0, ($page - 1) * $perPage);
}

/**
 * Render pagination HTML
 */
function renderPagination(int $total, int $page, int $perPage, string $baseUrl): string
{
    $totalPages = (int) ceil($total / $perPage);
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav><ul class="pagination justify-content-center">';
    $separator = str_contains($baseUrl, '?') ? '&' : '?';

    if ($page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . $separator . 'page=' . ($page - 1)) . '">&laquo;</a></li>';
    }

    for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
        $active = $i === $page ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($baseUrl . $separator . 'page=' . $i) . '">' . $i . '</a></li>';
    }

    if ($page < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl . $separator . 'page=' . ($page + 1)) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Track page view
 */
function trackPageView(?int $newsId = null, string $pageType = 'page'): void
{
    try {
        $model = new ReportModel();
        $model->trackVisit(getClientIp(), $newsId, $pageType);
    } catch (Exception $e) {
        // Silently fail analytics
    }
}

/**
 * Get asset URL with cache bust
 */
function asset(string $path): string
{
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/**
 * Check maintenance mode
 */
function checkMaintenanceMode(): void
{
    if (setting('maintenance_mode') === '1' && !Auth::check()) {
        http_response_code(503);
        require VIEWS_PATH . '/frontend/maintenance.php';
        exit;
    }
}

/**
 * Extract YouTube video ID
 */
function getYoutubeId(string $url): ?string
{
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return $matches[1] ?? null;
}

/**
 * Time ago format
 */
function timeAgo(?string $datetime): string
{
    if (empty($datetime)) {
        return '';
    }
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDate($datetime);
}
