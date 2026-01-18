<?php
namespace Core;

class Router
{
    private array $routes = [];
    private array $params = [];

    /**
     * Registrar ruta GET
     */
    public function get(string $uri, string $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    /**
     * Registrar ruta POST
     */
    public function post(string $uri, string $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    /**
     * Agregar ruta al registro
     */
    private function addRoute(string $method, string $uri, string $action, array $middleware): self
    {
        $this->routes[] = [
            'method'     => $method,
            'uri'        => $this->normalizeUri($uri),
            'action'     => $action,
            'middleware' => $middleware,
        ];
        return $this;
    }

    /**
     * Cargar rutas desde array de configuración
     */
    public function loadRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $method = strtolower($route['method']);
            $this->$method(
                $route['uri'],
                $route['action'],
                $route['middleware'] ?? []
            );
        }
    }

    /**
     * Resolver la URI actual y encontrar la ruta correspondiente
     */
    public function resolve(string $uri, string $method): ?array
    {
        $uri = $this->normalizeUri($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if ($this->matchUri($route['uri'], $uri)) {
                $this->params = $this->extractParams($route['uri'], $uri);
                return $route;
            }
        }

        return null;
    }

    /**
     * Verificar si la URI coincide con el patrón de ruta
     */
    private function matchUri(string $routeUri, string $requestUri): bool
    {
        $pattern = $this->uriToRegex($routeUri);
        return preg_match($pattern, $requestUri) === 1;
    }

    /**
     * Convertir URI con parámetros a expresión regular
     */
    private function uriToRegex(string $uri): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Extraer parámetros de la URI
     */
    private function extractParams(string $routeUri, string $requestUri): array
    {
        $pattern = $this->uriToRegex($routeUri);
        preg_match($pattern, $requestUri, $matches);

        return array_filter($matches, function ($key) {
            return is_string($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Obtener parámetros extraídos
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Normalizar URI (quitar slash inicial/final)
     */
    private function normalizeUri(string $uri): string
    {
        return '/' . trim($uri, '/');
    }

    /**
     * Obtener todas las rutas registradas
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
