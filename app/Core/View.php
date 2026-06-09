<?php

namespace App\Core;

class View
{
    /**
     * Renderizar una vista cargando los datos en variables y pasándolo por un layout
     */
    public function render(string $view, array $data = [], string $layout = 'main')
    {
        // Convertir las claves del array de datos en variables independientes
        extract($data);

        // Ruta completa de la vista a renderizar
        $viewFile = ROOT_DIR . '/app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            die("Error: La vista {$view} no existe en el directorio de vistas.");
        }

        // Iniciar el búfer de salida para capturar la vista renderizada
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Si se define un layout, renderizarlo inyectando la vista capturada
        if ($layout) {
            $layoutFile = ROOT_DIR . '/app/Views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                die("Error: El layout {$layout} no existe en la carpeta de layouts.");
            }
        } else {
            // Si no hay layout, imprimir el contenido directamente
            echo $content;
        }
    }
}
