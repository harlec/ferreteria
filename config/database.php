<?php
/**
 * Configuración de base de datos
 *
 * En producción, se recomienda usar variables de entorno:
 * - DB_HOST
 * - DB_NAME
 * - DB_USER
 * - DB_PASS
 */

return [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_NAME') ?: 'admin_ferrew',
    'username' => getenv('DB_USER') ?: 'admin_ferrew',
    'password' => getenv('DB_PASS') ?: 'ikm169uhn',
    'encoding' => 'utf8',
    'port'     => getenv('DB_PORT') ?: 3306,
];
