<?php
namespace App\Helpers;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Obtener valor de sesión
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Establecer valor en sesión
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Verificar si existe una clave
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar valor de sesión
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Limpiar toda la sesión
     */
    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * Destruir la sesión completamente
     */
    public function destroy(): void
    {
        $this->clear();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Establecer mensaje flash (disponible solo en siguiente request)
     */
    public function flash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Obtener mensaje flash
     */
    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Verificar si hay mensaje flash
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Regenerar ID de sesión (para seguridad)
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Obtener ID de sesión
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Obtener todos los datos de sesión
     */
    public function all(): array
    {
        return $_SESSION;
    }
}
