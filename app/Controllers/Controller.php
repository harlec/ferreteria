<?php
namespace App\Controllers;

use Core\View;
use Core\Request;
use App\Helpers\Session;
use App\Helpers\Validator;

abstract class Controller
{
    protected View $view;
    protected Request $request;
    protected Session $session;

    public function __construct()
    {
        $this->view = new View();
        $this->request = new Request();
        $this->session = new Session();

        // Compartir datos globales con las vistas
        $this->view->share('session', $this->session);
    }

    /**
     * Renderizar vista con layout
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $this->view->render($view, $data, $layout);
    }

    /**
     * Renderizar vista parcial (sin layout)
     */
    protected function partial(string $view, array $data = []): void
    {
        $this->view->partial($view, $data);
    }

    /**
     * Respuesta JSON para AJAX
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redireccionar a otra URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redireccionar con mensaje flash
     */
    protected function redirectWith(string $url, string $type, string $message): void
    {
        $this->session->flash($type, $message);
        $this->redirect($url);
    }

    /**
     * Verificar si es petición AJAX
     */
    protected function isAjax(): bool
    {
        return $this->request->isAjax();
    }

    /**
     * Obtener usuario actual de la sesión
     */
    protected function currentUser(): ?array
    {
        if (!$this->session->get('ingress')) {
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
     * Verificar si el usuario actual es admin
     */
    protected function isAdmin(): bool
    {
        return $this->session->get('type') === 'admin';
    }

    /**
     * Validar datos de entrada
     */
    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator();
        return $validator->validate($data, $rules);
    }

    /**
     * Verificar token CSRF
     */
    protected function verifyCsrf(): bool
    {
        $token = $this->request->post('_token') ??
                 $this->request->header('X-CSRF-TOKEN');

        if (empty($token)) {
            return false;
        }

        return hash_equals($this->session->get('csrf_token') ?? '', $token);
    }

    /**
     * Abortar con error
     */
    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);

        if ($this->isAjax()) {
            $this->json(['error' => $message ?: 'Error'], $code);
        }

        $messages = [
            400 => 'Solicitud inválida',
            401 => 'No autorizado',
            403 => 'Acceso denegado',
            404 => 'Recurso no encontrado',
            500 => 'Error del servidor',
        ];

        $title = $messages[$code] ?? 'Error';

        echo "<h1>{$code} - {$title}</h1>";
        if ($message) {
            echo "<p>{$message}</p>";
        }
        exit;
    }

    /**
     * Obtener datos de entrada filtrados
     */
    protected function input(string $key = null, $default = null)
    {
        return $this->request->input($key, $default);
    }

    /**
     * Obtener solo ciertos campos de la entrada
     */
    protected function only(array $keys): array
    {
        return $this->request->only($keys);
    }
}
