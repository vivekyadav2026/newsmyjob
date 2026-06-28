<?php
/**
 * Base Controller - common controller functionality
 */

declare(strict_types=1);

class BaseController
{
    /**
     * Render admin view with layout
     */
    protected function renderAdmin(string $view, array $data = [], string $pageTitle = 'Admin'): void
    {
        extract($data);
        require VIEWS_PATH . '/admin/includes/header.php';
        require VIEWS_PATH . '/admin/includes/sidebar.php';
        echo '<div class="admin-content">';
        require VIEWS_PATH . '/admin/includes/navbar.php';
        echo '<div class="p-4">';
        require VIEWS_PATH . '/admin/includes/alerts.php';
        require VIEWS_PATH . "/$view";
        echo '</div></div>';
        require VIEWS_PATH . '/admin/includes/footer.php';
    }

    /**
     * Redirect with flash message
     */
    protected function redirectWithMessage(string $url, string $type, string $message): never
    {
        Session::flash($type, $message);
        redirect($url);
    }
}
