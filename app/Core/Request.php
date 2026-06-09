<?php

namespace App\Core;

class Request
{
    /**
     * Obtener el método HTTP de la petición (GET, POST, etc.)
     */
    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Obtener la URI limpia de la petición, relativa a la carpeta raíz del proyecto
     */
    public function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Obtener el directorio base eliminando 'public/index.php'
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $baseDir = dirname(dirname($scriptName));
        
        // Limpiar baseDir de barras invertidas de Windows
        $baseDir = str_replace('\\', '/', $baseDir);
        $baseDir = rtrim($baseDir, '/');
        
        if (!empty($baseDir) && strpos($uri, $baseDir) === 0) {
            $uri = substr($uri, strlen($baseDir));
        }
        
        // Quitar parámetros query (?key=val)
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Asegurar que comience con '/' y quitar barra final si no es la raíz
        $uri = '/' . trim($uri, '/');
        
        return $uri;
    }

    /**
     * Obtener un valor de $_GET sanitizado
     */
    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->sanitize($_GET);
        }
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    /**
     * Obtener un valor de $_POST sanitizado
     */
    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->sanitize($_POST);
        }
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }

    /**
     * Obtener todos los parámetros de entrada fusionados y sanitizados
     */
    public function all(): array
    {
        $data = array_merge($_GET, $_POST);
        return $this->sanitize($data);
    }

    /**
     * Sanitizar recursivamente datos para evitar ataques XSS
     */
    private function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
            return $data;
        }
        
        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }
    
    /**
     * Obtener datos en bruto de la petición (útil para JSON en peticiones AJAX)
     */
    public function getJson(): array
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $this->sanitize($decoded);
        }
        return [];
    }
}
