<?php
namespace App\Middleware;

class AdminMiddleware
{
    /**
     * Verificar si el usuario es administrador
     */
    public function handle(): bool
    {
        if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
            // Si es AJAX, devolver JSON
            if ($this->isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'respuesta' => false,
                    'mensaje'   => 'Acceso denegado. Se requieren permisos de administrador.'
                ]);
                exit;
            }

            // Redirigir al dashboard
            header('Location: /dashboard');
            exit;
        }

        return true;
    }

    /**
     * Verificar si es petición AJAX
     */
    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
