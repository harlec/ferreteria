<?php
/**
 * Configuración general de la aplicación
 */

return [
    // Nombre de la aplicación
    'name' => 'Sistema Ferretería',

    // URL base (ajustar según el entorno)
    'base_url' => '/',

    // Zona horaria
    'timezone' => 'America/Lima',

    // Modo debug (desactivar en producción)
    'debug' => true,

    // Versión de la aplicación
    'version' => '2.0.0',

    // Configuración de sesión
    'session' => [
        'lifetime' => 120, // minutos
        'name'     => 'ferreteria_session',
    ],

    // Configuración de empresa (para comprobantes)
    'empresa' => [
        'nombre'    => 'Ferretería',
        'ruc'       => '',
        'direccion' => '',
        'telefono'  => '',
    ],
];
