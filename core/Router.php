<?php
namespace Core;

class Router
{
    private $routes = [];
    private $params = [];

    /**
     * Registrar ruta GET
     */
    public function get($uri, $action, $middleware = [])
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    /**
     * Registrar ruta POST
     */
    public function post($uri, $action, $middleware = [])
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    /**
     * Agregar ruta al registro
     */
    private function addRoute($method, $uri, $action, $middleware)
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
    public function loadRoutes($routes)
    {
        foreach ($routes as $route) {
            $method = strtolower($route['method']);
            $middleware = isset($route['middleware']) ? $route['middleware'] : [];
            $this->$method($route['uri'], $route['action'], $middleware);
        }
    }

    /**
     * Resolver la URI actual y encontrar la ruta correspondiente
     */
    public function resolve($uri, $method)
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
    private function matchUri($routeUri, $requestUri)
    {
        $pattern = $this->uriToRegex($routeUri);
        return preg_match($pattern, $requestUri) === 1;
    }

    /**
     * Convertir URI con parámetros a expresión regular
     */
    private function uriToRegex($uri)
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Extraer parámetros de la URI
     */
    private function extractParams($routeUri, $requestUri)
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
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Normalizar URI (quitar slash inicial/final)
     */
    private function normalizeUri($uri)
    {
        return '/' . trim($uri, '/');
    }

    /**
     * Obtener todas las rutas registradas
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
