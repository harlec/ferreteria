<?php
/**
 * Archivo de diagnóstico temporal
 * Eliminar después de verificar
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>Diagnóstico del Sistema</h2>";

// Test 1: Verificar versión de PHP
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Test 2: Verificar autoload
echo "<h3>Test Autoload</h3>";
try {
    require_once dirname(__DIR__) . '/autoload.php';
    echo "<p style='color:green'>✓ Autoload cargado correctamente</p>";
    echo "<p>BASE_PATH: " . BASE_PATH . "</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>✗ Error en autoload: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Test 3: Verificar configuración
echo "<h3>Test Configuración</h3>";
try {
    $config = require BASE_PATH . '/config/app.php';
    echo "<p style='color:green'>✓ Configuración cargada</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>✗ Error en config: " . $e->getMessage() . "</p>";
    exit;
}

// Test 4: Verificar SDBA
echo "<h3>Test SDBA (Base de datos)</h3>";
try {
    $test = Sdba::table('usuarios')->get_one();
    if ($test) {
        echo "<p style='color:green'>✓ Conexión a BD exitosa</p>";
        echo "<p>Usuario encontrado: " . ($test['usuario'] ?? 'N/A') . "</p>";
    } else {
        echo "<p style='color:orange'>⚠ BD conectada pero no hay usuarios</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>✗ Error de BD: " . $e->getMessage() . "</p>";
}

// Test 5: Verificar clases Core
echo "<h3>Test Clases Core</h3>";
$coreClasses = [
    'Core\\App',
    'Core\\Router',
    'Core\\Request',
    'Core\\View',
];
foreach ($coreClasses as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>✓ {$class}</p>";
    } else {
        echo "<p style='color:red'>✗ {$class} no encontrada</p>";
    }
}

// Test 6: Verificar controladores
echo "<h3>Test Controladores</h3>";
$controllers = [
    'App\\Controllers\\Controller',
    'App\\Controllers\\AuthController',
    'App\\Controllers\\DashboardController',
];
foreach ($controllers as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>✓ {$class}</p>";
    } else {
        echo "<p style='color:red'>✗ {$class} no encontrada</p>";
    }
}

// Test 7: Verificar modelos
echo "<h3>Test Modelos</h3>";
$models = [
    'App\\Models\\Model',
    'App\\Models\\Usuario',
];
foreach ($models as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>✓ {$class}</p>";
    } else {
        echo "<p style='color:red'>✗ {$class} no encontrada</p>";
    }
}

// Test 8: Verificar helpers
echo "<h3>Test Helpers</h3>";
$helpers = [
    'App\\Helpers\\Session',
    'App\\Helpers\\Validator',
    'App\\Helpers\\Response',
];
foreach ($helpers as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>✓ {$class}</p>";
    } else {
        echo "<p style='color:red'>✗ {$class} no encontrada</p>";
    }
}

// Test 9: Verificar services
echo "<h3>Test Services</h3>";
$services = [
    'App\\Services\\AuthService',
];
foreach ($services as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>✓ {$class}</p>";
    } else {
        echo "<p style='color:red'>✗ {$class} no encontrada</p>";
    }
}

// Test 10: Verificar middleware
echo "<h3>Test Middleware</h3>";
$middleware = [
    'App\\Middleware\\AuthMiddleware',
    'App\\Middleware\\AdminMiddleware',
    'App\\Middleware\\CsrfMiddleware',
];
foreach ($middleware as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>✓ {$class}</p>";
    } else {
        echo "<p style='color:red'>✗ {$class} no encontrada</p>";
    }
}

// Test 11: Verificar vistas
echo "<h3>Test Vistas</h3>";
$views = [
    '/app/Views/layouts/main.php',
    '/app/Views/layouts/auth.php',
    '/app/Views/auth/login.php',
    '/app/Views/dashboard/index.php',
];
foreach ($views as $view) {
    $path = BASE_PATH . $view;
    if (file_exists($path)) {
        echo "<p style='color:green'>✓ {$view}</p>";
    } else {
        echo "<p style='color:red'>✗ {$view} no encontrada</p>";
    }
}

echo "<hr><p><strong>Diagnóstico completado.</strong></p>";
echo "<p><em>Elimina este archivo (test.php) después de verificar.</em></p>";
