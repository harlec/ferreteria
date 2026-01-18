<?php
/**
 * Definición de rutas de la aplicación
 *
 * Formato:
 * [
 *     'method'     => 'GET|POST',
 *     'uri'        => '/ruta/{parametro}',
 *     'action'     => 'ControllerName@methodName',
 *     'middleware' => ['auth', 'admin'] // opcional
 * ]
 */

return [
    // ===================================
    // AUTENTICACIÓN
    // ===================================
    [
        'method' => 'GET',
        'uri'    => '/',
        'action' => 'AuthController@showLogin',
    ],
    [
        'method' => 'GET',
        'uri'    => '/login',
        'action' => 'AuthController@showLogin',
    ],
    [
        'method' => 'POST',
        'uri'    => '/login',
        'action' => 'AuthController@login',
    ],
    [
        'method'     => 'GET',
        'uri'        => '/logout',
        'action'     => 'AuthController@logout',
        'middleware' => ['auth'],
    ],

    // ===================================
    // DASHBOARD
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/dashboard',
        'action'     => 'DashboardController@index',
        'middleware' => ['auth'],
    ],

    // ===================================
    // USUARIOS
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/usuarios',
        'action'     => 'UsuarioController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/usuarios/crear',
        'action'     => 'UsuarioController@create',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/usuarios',
        'action'     => 'UsuarioController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/usuarios/{id}/editar',
        'action'     => 'UsuarioController@edit',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/usuarios/{id}',
        'action'     => 'UsuarioController@update',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/usuarios/{id}/eliminar',
        'action'     => 'UsuarioController@destroy',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // CLIENTES
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/clientes',
        'action'     => 'ClienteController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/clientes/crear',
        'action'     => 'ClienteController@create',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/clientes',
        'action'     => 'ClienteController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/clientes/{id}',
        'action'     => 'ClienteController@show',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/clientes/{id}/editar',
        'action'     => 'ClienteController@edit',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/clientes/{id}',
        'action'     => 'ClienteController@update',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/clientes/{id}/eliminar',
        'action'     => 'ClienteController@destroy',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // PRODUCTOS
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/productos',
        'action'     => 'ProductoController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/productos/crear',
        'action'     => 'ProductoController@create',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/productos',
        'action'     => 'ProductoController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/productos/{id}',
        'action'     => 'ProductoController@show',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/productos/{id}/editar',
        'action'     => 'ProductoController@edit',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/productos/{id}',
        'action'     => 'ProductoController@update',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/productos/{id}/eliminar',
        'action'     => 'ProductoController@destroy',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // CATEGORÍAS
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/categorias',
        'action'     => 'CategoriaController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/categorias',
        'action'     => 'CategoriaController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/categorias/{id}',
        'action'     => 'CategoriaController@update',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/categorias/{id}/eliminar',
        'action'     => 'CategoriaController@destroy',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // MARCAS
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/marcas',
        'action'     => 'MarcaController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/marcas',
        'action'     => 'MarcaController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/marcas/{id}',
        'action'     => 'MarcaController@update',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/marcas/{id}/eliminar',
        'action'     => 'MarcaController@destroy',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // COLORES
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/colores',
        'action'     => 'ColorController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/colores',
        'action'     => 'ColorController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/colores/{id}',
        'action'     => 'ColorController@update',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/colores/{id}/eliminar',
        'action'     => 'ColorController@destroy',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // PROVEEDORES
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/proveedores',
        'action'     => 'ProveedorController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/proveedores/crear',
        'action'     => 'ProveedorController@create',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/proveedores',
        'action'     => 'ProveedorController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/proveedores/{id}/editar',
        'action'     => 'ProveedorController@edit',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/proveedores/{id}',
        'action'     => 'ProveedorController@update',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // VENTAS
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/ventas',
        'action'     => 'VentaController@index',
        'middleware' => ['auth'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/ventas/crear',
        'action'     => 'VentaController@create',
        'middleware' => ['auth'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/ventas',
        'action'     => 'VentaController@store',
        'middleware' => ['auth'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/ventas/{id}',
        'action'     => 'VentaController@show',
        'middleware' => ['auth'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/ventas/{id}/anular',
        'action'     => 'VentaController@anular',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // COMPRAS
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/compras',
        'action'     => 'CompraController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/compras/crear',
        'action'     => 'CompraController@create',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'POST',
        'uri'        => '/compras',
        'action'     => 'CompraController@store',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/compras/{id}',
        'action'     => 'CompraController@show',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // REPORTES
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/reportes',
        'action'     => 'ReporteController@index',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/reportes/ventas',
        'action'     => 'ReporteController@ventas',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/reportes/compras',
        'action'     => 'ReporteController@compras',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/reportes/stock',
        'action'     => 'ReporteController@stock',
        'middleware' => ['auth', 'admin'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/reportes/kardex',
        'action'     => 'ReporteController@kardex',
        'middleware' => ['auth', 'admin'],
    ],

    // ===================================
    // API AJAX
    // ===================================
    [
        'method'     => 'GET',
        'uri'        => '/api/productos/buscar',
        'action'     => 'ProductoController@buscar',
        'middleware' => ['auth'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/api/productos/{id}/precio',
        'action'     => 'ProductoController@getPrecio',
        'middleware' => ['auth'],
    ],
    [
        'method'     => 'GET',
        'uri'        => '/api/clientes/buscar',
        'action'     => 'ClienteController@buscar',
        'middleware' => ['auth'],
    ],
];
