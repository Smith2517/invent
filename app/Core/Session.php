<?php

namespace App\Core;

class Session
{
    /**
     * Inicializar la sesión con configuraciones de seguridad
     */
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurar cookies de sesión seguras
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            // Habilitar samesite lax
            if (PHP_VERSION_ID >= 70300) {
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            } else {
                session_set_cookie_params(0, '/; SameSite=Lax', '', isset($_SERVER['HTTPS']), true);
            }
            
            session_start();
        }
    }

    /**
     * Establecer un valor en la sesión
     */
    public static function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener un valor de la sesión
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe una clave en la sesión
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar un valor de la sesión
     */
    public static function remove(string $key)
    {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destruir completamente la sesión
     */
    public static function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Establecer un mensaje flash (persiste solo para la siguiente petición)
     */
    public static function setFlash(string $key, string $message)
    {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Obtener y remover un mensaje flash
     */
    public static function getFlash(string $key)
    {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }

    /**
     * Verificar si hay mensajes flash de un tipo específico
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Obtener o generar un token CSRF para la sesión
     */
    public static function csrfToken(): string
    {
        if (!self::has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            self::set('csrf_token', $token);
        }
        return self::get('csrf_token');
    }
}
