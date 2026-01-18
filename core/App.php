<?php
namespace Core;

class App
{
    public $router;
    public $request;
    private $config;
    private $middleware = [];

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->router = new Router();
        $this->request = new Request();

        // Definir URL base
        if (!defined('BASE_URL')) {
            $baseUrl = isset($config['base_url']) ? $config['base_url'] : '/';
            define('BASE_URL', $baseUrl);
        }

        // Configurar timezone
        if (isset($config['timezone'])) {
            date_default_timezone_set($config['timezone']);
        }

        // Configurar errores
        $debug = isset($config['debug']) ? $config['debug'] : false;
        if ($debug) {
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
    public function registerMiddleware($name, $class)
    {
        $this->middleware[$name] = $class;
    }

    /**
     * Ejecutar la aplicación
     */
    public function run()
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
        $middlewareList = isset($route['middleware']) ? $route['middleware'] : [];
        if (!$this->runMiddleware($middlewareList)) {
            return;
        }

        // Ejecutar acción del controlador
        $this->dispatch($route['action'], $this->router->getParams());
    }

    /**
     * Ejecutar middleware de la ruta
     */
    private function runMiddleware($middlewareList)
    {
        foreach ($middlewareList as $name) {
            $class = isset($this->middleware[$name]) ? $this->middleware[$name] : null;

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
    private function dispatch($action, $params)
    {
        // Parsear Controller@method
        $parts = explode('@', $action);
        $controllerName = $parts[0];
        $method = $parts[1];

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
    private function notFound()
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
    private function error($message)
    {
        http_response_code(500);

        $debug = isset($this->config['debug']) ? $this->config['debug'] : false;
        if ($debug) {
            echo "<h1>Error</h1><p>{$message}</p>";
        } else {
            echo '<h1>Error del servidor</h1>';
        }
    }

    /**
     * Obtener configuración
     */
    public function config($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
}
