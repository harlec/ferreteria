<?php
namespace Core;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $files;

    public function __construct()
    {
        $this->get = $this->sanitize($_GET);
        $this->post = $this->sanitize($_POST);
        $this->server = $_SERVER;
        $this->files = $_FILES;
    }

    /**
     * Sanitizar array de entrada
     */
    private function sanitize(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $sanitized;
    }

    /**
     * Obtener valor de GET
     */
    public function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    /**
     * Obtener valor de POST
     */
    public function post(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    /**
     * Obtener valor de cualquier método (GET o POST)
     */
    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($this->get, $this->post);
        }
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    /**
     * Verificar si existe un campo
     */
    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    /**
     * Obtener todos los datos de entrada
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Obtener solo ciertos campos
     */
    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->input($key);
            }
        }
        return $result;
    }

    /**
     * Obtener todos excepto ciertos campos
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    /**
     * Obtener archivo subido
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Verificar si hay archivo subido
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Obtener método HTTP
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Obtener URI actual
     */
    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        return '/' . trim($uri, '/');
    }

    /**
     * Verificar si es petición AJAX
     */
    public function isAjax(): bool
    {
        return !empty($this->server['HTTP_X_REQUESTED_WITH']) &&
            strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verificar si es petición POST
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Verificar si es petición GET
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Obtener IP del cliente
     */
    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ??
            $this->server['HTTP_CLIENT_IP'] ??
            $this->server['REMOTE_ADDR'] ??
            '0.0.0.0';
    }

    /**
     * Obtener User Agent
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Obtener header específico
     */
    public function header(string $key, $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }
}
