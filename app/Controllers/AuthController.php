<?php
namespace App\Controllers;

use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    /**
     * Mostrar formulario de login
     */
    public function showLogin(): void
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->authService->check()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', [
            'titulo' => 'Iniciar Sesión'
        ], 'auth');
    }

    /**
     * Procesar login
     */
    public function login(): void
    {
        $username = $this->request->post('usuario', '');
        $password = $this->request->post('pass', '');

        // Validar campos requeridos
        if (empty($username) || empty($password)) {
            $this->json([
                'respuesta' => false,
                'mensaje'   => 'Por favor ingrese usuario y contraseña'
            ]);
            return;
        }

        // Intentar autenticar
        $result = $this->authService->attempt($username, $password);

        $this->json([
            'respuesta' => $result['success'],
            'mensaje'   => $result['message']
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/login');
    }
}
