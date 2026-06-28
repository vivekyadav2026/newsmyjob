<?php
/**
 * Simple URL Router for Frontend
 */

declare(strict_types=1);

class Router
{
    private array $routes = [];

    /**
     * Add GET route
     */
    public function get(string $pattern, callable $handler): void
    {
        $this->routes['GET'][$pattern] = $handler;
    }

    /**
     * Add POST route
     */
    public function post(string $pattern, callable $handler): void
    {
        $this->routes['POST'][$pattern] = $handler;
    }

    /**
     * Dispatch request
     */
    public function dispatch(string $uri, string $method = 'GET'): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $uri = rtrim($uri, '/') ?: '/';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                call_user_func_array($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        require VIEWS_PATH . '/frontend/404.php';
    }
}
