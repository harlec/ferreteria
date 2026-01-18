<?php
/**
 * Front Controller - Punto de entrada único del sistema MVC
 * Sistema de Ferretería
 */

// Mostrar errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Cargar autoloader
require_once dirname(__DIR__) . '/autoload.php';

// Cargar configuración
$config = require BASE_PATH . '/config/app.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Crear instancia de la aplicación
$app = new Core\App($config);

// Registrar middleware
$app->registerMiddleware('auth', App\Middleware\AuthMiddleware::class);
$app->registerMiddleware('admin', App\Middleware\AdminMiddleware::class);
$app->registerMiddleware('csrf', App\Middleware\CsrfMiddleware::class);

// Cargar rutas
$routes = require BASE_PATH . '/config/routes.php';
$app->router->loadRoutes($routes);

// Ejecutar aplicación
$app->run();
