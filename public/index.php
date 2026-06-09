<?php
/**
 * Front Controller - Punto de Entrada Único del Sistema
 */

// Cargar configuración global
require_once dirname(__DIR__) . '/app/Config/Config.php';

// Autocargador PSR-4 básico para namespace App
spl_autoload_register(function ($class) {
    // Prefijo de namespace del proyecto
    $prefix = 'App\\';
    
    // Directorio base para el prefijo de namespace
    $base_dir = dirname(__DIR__) . '/app/';
    
    // Verificar si la clase utiliza el prefijo
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // No pertenece a este namespace
    }
    
    // Obtener el nombre relativo de la clase
    $relative_class = substr($class, $len);
    
    // Reemplazar separadores de namespace por separadores de directorio y añadir .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si el archivo existe, requerirlo
    if (file_exists($file)) {
        require_once $file;
    }
});

// Inicializar la sesión de forma segura
App\Core\Session::init();

// Inicializar y ejecutar el enrutador
$router = new App\Core\Router();

// Cargar las rutas definidas
require_once dirname(__DIR__) . '/app/Config/Routes.php';

// Resolver la petición
$router->dispatch();
