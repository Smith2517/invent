<?php
/**
 * Configuración global de la aplicación
 */

// Definición de zona horaria
date_default_timezone_set('America/Lima');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_patrimonio');
define('DB_CHARSET', 'utf8mb4');

// URL Base del Sistema (Detectar dinámicamente protocolo, host y puerto)
$protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $host . '/invent');

// Directorio Raíz del Servidor
define('ROOT_DIR', dirname(dirname(__DIR__)));

// Duración máxima de la sesión por inactividad (en segundos: 45 minutos = 2700s)
define('SESSION_TIMEOUT', 2700);

// Nivel de entorno: development o production
define('APP_ENV', 'development');

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}
