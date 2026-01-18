<?php
/**
 * PSR-4 Autoloader para el sistema MVC Ferretería
 */

define('BASE_PATH', __DIR__);

spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\Controllers\\' => BASE_PATH . '/app/Controllers/',
        'App\\Models\\'      => BASE_PATH . '/app/Models/',
        'App\\Middleware\\'  => BASE_PATH . '/app/Middleware/',
        'App\\Services\\'    => BASE_PATH . '/app/Services/',
        'App\\Helpers\\'     => BASE_PATH . '/app/Helpers/',
        'Core\\'             => BASE_PATH . '/core/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Cargar SDBA ORM
require_once BASE_PATH . '/vendor/sdba/sdba.php';

// Cargar helpers globales
if (file_exists(BASE_PATH . '/app/Helpers/functions.php')) {
    require_once BASE_PATH . '/app/Helpers/functions.php';
}
