<?php
/**
 * Main Application Configuration
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Environment
define('APP_ENV', 'production');
define('APP_DEBUG', false);

// Base URLs - adjust for your XAMPP setup
// define('BASE_URL', 'http://localhost/newsmyjob/');
define('BASE_URL', 'https://myjobhub.in');
define('ADMIN_URL', BASE_URL . '/admin');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Paths
define('UPLOADS_PATH', APP_ROOT . '/uploads');
define('VIEWS_PATH', APP_ROOT . '/views');

// Session
define('SESSION_NAME', 'NEWSMYJOB_SESSION');
define('SESSION_LIFETIME', 7200); // 2 hours
define('REMEMBER_ME_DAYS', 30);

// Security
define('CSRF_TOKEN_NAME', '_csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Pagination
define('ADMIN_PER_PAGE', 15);
define('FRONTEND_PER_PAGE', 12);

// Upload limits
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_DOC_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
