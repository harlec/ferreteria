<?php
namespace Core;

class View
{
    private string $viewsPath;
    private string $layoutsPath;
    private array $sharedData = [];

    public function __construct()
    {
        $this->viewsPath = BASE_PATH . '/app/Views/';
        $this->layoutsPath = BASE_PATH . '/app/Views/layouts/';
    }

    /**
     * Compartir datos globalmente con todas las vistas
     */
    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    /**
     * Renderizar vista con layout
     */
    public function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Combinar datos compartidos con datos de la vista
        $data = array_merge($this->sharedData, $data);

        // Extraer datos para la vista
        extract($data);

        // Hacer disponible $this en la vista
        $__view = $this;

        // Capturar contenido de la vista
        ob_start();
        $viewFile = $this->viewsPath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("Vista no encontrada: {$view}");
        }

        require $viewFile;
        $content = ob_get_clean();

        // Renderizar layout con contenido
        $layoutFile = $this->layoutsPath . $layout . '.php';

        if (!file_exists($layoutFile)) {
            // Si no hay layout, mostrar solo el contenido
            echo $content;
            return;
        }

        require $layoutFile;
    }

    /**
     * Renderizar vista sin layout (para AJAX)
     */
    public function partial(string $view, array $data = []): void
    {
        $data = array_merge($this->sharedData, $data);
        extract($data);

        $__view = $this;

        $viewFile = $this->viewsPath . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        }
    }

    /**
     * Incluir partial (header, footer, etc.)
     */
    public function include(string $partial, array $data = []): void
    {
        extract(array_merge($this->sharedData, $data));

        $__view = $this;

        $partialFile = $this->layoutsPath . 'partials/' . $partial . '.php';

        if (file_exists($partialFile)) {
            require $partialFile;
        }
    }

    /**
     * Escapar HTML para prevenir XSS
     */
    public function e(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generar URL base
     */
    public function url(string $path = ''): string
    {
        $base = rtrim(BASE_URL, '/');
        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Generar URL de asset
     */
    public function asset(string $path): string
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }

    /**
     * Generar token CSRF
     */
    public function csrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Generar campo hidden con token CSRF
     */
    public function csrfField(): string
    {
        return '<input type="hidden" name="_token" value="' . $this->csrf() . '">';
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public function auth(): bool
    {
        return isset($_SESSION['ingress']) && $_SESSION['ingress'] === true;
    }

    /**
     * Obtener usuario actual
     */
    public function user(): ?array
    {
        if (!$this->auth()) {
            return null;
        }
        return [
            'id'      => $_SESSION['id_usr'] ?? null,
            'usuario' => $_SESSION['usuario'] ?? null,
            'nombres' => $_SESSION['nombres'] ?? null,
            'tipo'    => $_SESSION['type'] ?? null,
            'tienda'  => $_SESSION['tienda'] ?? null,
        ];
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return isset($_SESSION['type']) && $_SESSION['type'] === 'admin';
    }

    /**
     * Formatear número como moneda
     */
    public function money(mixed $amount, int $decimals = 2): string
    {
        return 'S/ ' . number_format((float)$amount, $decimals, '.', ',');
    }

    /**
     * Formatear fecha
     */
    public function date(?string $date, string $format = 'd/m/Y'): string
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }
        return date($format, strtotime($date));
    }
}
