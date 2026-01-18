<?php
namespace App\Helpers;

class Response
{
    /**
     * Enviar respuesta JSON
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Respuesta exitosa
     */
    public static function success($data = null, string $message = 'Operación exitosa'): void
    {
        self::json([
            'respuesta' => true,
            'mensaje'   => $message,
            'data'      => $data,
        ]);
    }

    /**
     * Respuesta de error
     */
    public static function error(string $message = 'Error', int $status = 400, array $errors = []): void
    {
        $response = [
            'respuesta' => false,
            'mensaje'   => $message,
        ];

        if (!empty($errors)) {
            $response['errores'] = $errors;
        }

        self::json($response, $status);
    }

    /**
     * Redireccionar
     */
    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Descargar archivo
     */
    public static function download(string $filePath, string $fileName = null): void
    {
        if (!file_exists($filePath)) {
            self::error('Archivo no encontrado', 404);
        }

        $fileName = $fileName ?? basename($filePath);
        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    /**
     * Mostrar PDF en navegador
     */
    public static function pdf(string $content, string $fileName = 'documento.pdf'): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($content));

        echo $content;
        exit;
    }

    /**
     * Respuesta vacía
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }
}
