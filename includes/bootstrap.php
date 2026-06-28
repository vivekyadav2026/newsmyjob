<?php
/**
 * Application Bootstrap
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Session.php';
require_once APP_ROOT . '/includes/Security.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Router.php';
require_once APP_ROOT . '/functions/helpers.php';
require_once APP_ROOT . '/functions/validation.php';
require_once APP_ROOT . '/functions/compat.php';

spl_autoload_register(function (string $class): void {
    foreach (['/models/', '/controllers/'] as $dir) {
        $file = APP_ROOT . $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Session::start();

if (!Auth::check()) {
    Auth::attemptRememberLogin();
}
