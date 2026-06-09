<?php

namespace App\Core;

class Response
{
    /**
     * Establecer el código de estado HTTP de la respuesta
     */
    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    /**
     * Redirigir a una URL interna o externa
     */
    public function redirect(string $url)
    {
        // Si la URL es relativa, agregar el BASE_URL
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = BASE_URL . '/' . ltrim($url, '/');
        }
        
        header('Location: ' . $url);
        exit;
    }

    /**
     * Enviar una respuesta en formato JSON
     */
    public function json(array $data, int $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->setStatusCode($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Renderizar una respuesta JSON de error estándar
     */
    public function error(string $message, int $statusCode = 400)
    {
        $this->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Renderizar una respuesta JSON de éxito estándar
     */
    public function success(string $message = '', array $data = [], int $statusCode = 200)
    {
        $response = ['success' => true];
        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($data)) {
            $response['data'] = $data;
        }
        $this->json($response, $statusCode);
    }
}
