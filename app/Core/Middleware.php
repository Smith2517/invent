<?php

namespace App\Core;

abstract class Middleware
{
    /**
     * Procesar la petición y decidir si continúa o interrumpe la ejecución
     *
     * @param Request $request
     * @param Response $response
     * @param array $permissions Permisos requeridos para la ruta
     * @param callable $next Siguiente middleware o acción del controlador
     */
    abstract public function handle(Request $request, Response $response, array $permissions, callable $next);
}
