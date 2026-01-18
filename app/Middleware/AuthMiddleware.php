<?php
namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Verificar si el usuario está autenticado
     */
    public function handle(): bool
    {
        if (!isset($_SESSION['ingress']) || $_SESSION['ingress'] !== true) {
            // Si es AJAX, devolver JSON
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'respuesta' => false,
                    'mensaje'   => 'No autorizado. Por favor inicie sesión.'
                ]);
                exit;
            }

            // Redirigir al login
            header('Location: /login');
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
