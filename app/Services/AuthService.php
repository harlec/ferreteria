<?php
namespace App\Services;

use App\Models\Usuario;
use App\Helpers\Session;

class AuthService
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * Intentar autenticar usuario
     */
    public function attempt(string $username, string $password): array
    {
        $user = Usuario::authenticate($username, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos',
            ];
        }

        // Crear sesión
        $this->createSession($user);

        return [
            'success' => true,
            'message' => 'Bienvenido ' . $user->nombres,
            'user'    => $user->toArray(),
        ];
    }

    /**
     * Crear sesión de usuario
     */
    private function createSession(Usuario $user): void
    {
        // Regenerar ID de sesión por seguridad
        $this->session->regenerate();

        // Establecer variables de sesión (compatibilidad con sistema legacy)
        $this->session->set('usuario', $user->usuario);
        $this->session->set('ingress', true);
        $this->session->set('nombres', $user->nombres);
        $this->session->set('id_usr', $user->getId());
        $this->session->set('type', $user->rol);
        $this->session->set('tienda', $user->tienda);
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        $this->session->destroy();
    }

    /**
     * Verificar si hay sesión activa
     */
    public function check(): bool
    {
        return $this->session->get('ingress') === true;
    }

    /**
     * Obtener usuario actual
     */
    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        return [
            'id'      => $this->session->get('id_usr'),
            'usuario' => $this->session->get('usuario'),
            'nombres' => $this->session->get('nombres'),
            'tipo'    => $this->session->get('type'),
            'tienda'  => $this->session->get('tienda'),
        ];
    }

    /**
     * Obtener ID del usuario actual
     */
    public function userId(): ?int
    {
        return $this->session->get('id_usr');
    }

    /**
     * Verificar si el usuario actual es admin
     */
    public function isAdmin(): bool
    {
        return $this->session->get('type') === 'admin';
    }

    /**
     * Verificar si el usuario actual es operador
     */
    public function isOperador(): bool
    {
        return $this->session->get('type') === 'operador';
    }

    /**
     * Obtener tienda del usuario actual
     */
    public function getTienda(): ?string
    {
        return $this->session->get('tienda');
    }
}
