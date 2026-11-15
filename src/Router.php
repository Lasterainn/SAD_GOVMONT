<?php
namespace App;

class Router {
    private array $routes = [];

    public function add(string $method, string $path, array $handler): void {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'controller' => $handler[0],
            'action'     => $handler[1],
        ];
    }

    public function dispatch(string $method, string $uri): void {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir)) ?: '/';
        }
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            if ($route['path'] === $path) {
                $controller = new $route['controller']();
                $action = $route['action'];
                $controller->$action();
                return;
            }

            if (str_contains($route['path'], '{')) {
                $pattern = '#^' . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route['path']) . '$#';
                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches);
                    $controller = new $route['controller']();
                    $action = $route['action'];
                    $controller->$action(...$matches);
                    return;
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
