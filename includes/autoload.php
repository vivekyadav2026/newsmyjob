<?php
/**
 * Model Autoloader
 */

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $file = APP_ROOT . '/models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
