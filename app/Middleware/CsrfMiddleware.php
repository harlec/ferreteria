<?php
namespace App\Middleware;

class CsrfMiddleware
{
    /**
     * Verificar token CSRF en peticiones POST
     */
    public function handle(): bool
    {
        // Solo verificar en peticiones POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (empty($token) || !$this->validateToken($token)) {
            if ($this->isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'respuesta' => false,
                    'mensaje'   => 'Token CSRF inválido'
                ]);
                exit;
            }

            http_response_code(403);
            die('Token CSRF inválido');
        }

        return true;
    }

    /**
     * Validar token CSRF
     */
    private function validateToken(string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        return hash_equals($sessionToken, $token);
    }

    /**
     * Generar token CSRF
     */
    public static function generateToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
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
