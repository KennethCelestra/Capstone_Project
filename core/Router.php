<?php

class Router
{
    private array $routes = [];

    public function loadRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Strip the base path (e.g. /clearance_system/public) from the URI
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $uri = '/' . ltrim(substr($uri, strlen($basePath)), '/');
        $uri = $uri === '' ? '/' : $uri;

        $routeKey = $method . ' ' . $uri;

        if (array_key_exists($routeKey, $this->routes)) {
            [$controllerName, $methodName] = $this->routes[$routeKey];
            $this->callController($controllerName, $methodName);
        } else {
            http_response_code(404);
            require_once ROOT_PATH . '/app/Views/errors/404.php';
        }
    }

    private function callController(string $controllerName, string $methodName): void
    {
        $controllerFile = ROOT_PATH . '/app/Controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            die("Controller not found: {$controllerName}");
        }
        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die("Class not found: {$controllerName}");
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $methodName)) {
            die("Method not found: {$controllerName}::{$methodName}");
        }

        $controller->{$methodName}();
    }
}
