<?php
/**
 * Simple Router - maps HTTP method + URI to controller actions
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Helpers;

class Router
{
    private static array $routes = [];

    public static function get(string $path, array $action): void
    {
        self::$routes[] = ['method' => 'GET', 'path' => $path, 'action' => $action];
    }

    public static function post(string $path, array $action): void
    {
        self::$routes[] = ['method' => 'POST', 'path' => $path, 'action' => $action];
    }

    public static function put(string $path, array $action): void
    {
        self::$routes[] = ['method' => 'PUT', 'path' => $path, 'action' => $action];
    }

    public static function delete(string $path, array $action): void
    {
        self::$routes[] = ['method' => 'DELETE', 'path' => $path, 'action' => $action];
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Support method override via _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach (self::$routes as $route) {
            $pattern = self::convertToRegex($route['path']);
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$controllerClass, $methodName] = $route['action'];
                $controller = new $controllerClass();
                $controller->$methodName(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        require VIEW_PATH . '/pages/404.php';
    }

    private static function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
