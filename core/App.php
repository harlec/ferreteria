<?php
namespace Core;

class App
{
    public Router $router;
    public Request $request;
    private array $config;
    private array $middleware = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->router = new Router();
        $this->request = new Request();

        // Definir URL base
        if (!defined('BASE_URL')) {
            define('BASE_URL', $config['base_url'] ?? '/');
        }

        // Configurar timezone
        if (isset($config['timezone'])) {
            date_default_timezone_set($config['timezone']);
        }

        // Configurar errores
        if ($config['debug'] ?? false) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Registrar middleware global
     */
    public function registerMiddleware(string $name, string $class): void
    {
        $this->middleware[$name] = $class;
    }

    /**
     * Ejecutar la aplicación
     */
    public function run(): void
    {
        $uri = $this->request->uri();
        $method = $this->request->method();

        // Buscar ruta
        $route = $this->router->resolve($uri, $method);

        if ($route === null) {
            $this->notFound();
            return;
        }

        // Ejecutar middleware
        if (!$this->runMiddleware($route['middleware'] ?? [])) {
            return;
        }

        // Ejecutar acción del controlador
        $this->dispatch($route['action'], $this->router->getParams());
    }

    /**
     * Ejecutar middleware de la ruta
     */
    private function runMiddleware(array $middlewareList): bool
    {
        foreach ($middlewareList as $name) {
            $class = $this->middleware[$name] ?? null;

            if ($class === null) {
                continue;
            }

            $middlewareInstance = new $class();

            if (!$middlewareInstance->handle()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Despachar la acción del controlador
     */
    private function dispatch(string $action, array $params): void
    {
        // Parsear Controller@method
        [$controllerName, $method] = explode('@', $action);

        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            $this->error("Controlador no encontrado: {$controllerName}");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            $this->error("Método no encontrado: {$method}");
            return;
        }

        // Llamar al método con parámetros
        call_user_func_array([$controller, $method], array_values($params));
    }

    /**
     * Mostrar página 404
     */
    private function notFound(): void
    {
        http_response_code(404);

        if ($this->request->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Recurso no encontrado']);
            return;
        }

        // Intentar mostrar vista 404
        $view404 = BASE_PATH . '/app/Views/errors/404.php';

        if (file_exists($view404)) {
            require $view404;
        } else {
            echo '<h1>404 - Página no encontrada</h1>';
        }
    }

    /**
     * Mostrar error
     */
    private function error(string $message): void
    {
        http_response_code(500);

        if ($this->config['debug'] ?? false) {
            echo "<h1>Error</h1><p>{$message}</p>";
        } else {
            echo '<h1>Error del servidor</h1>';
        }
    }

    /**
     * Obtener configuración
     */
    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
