<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware extends Middleware
{
    public function handle(Request $request, Response $response, array $permissions, callable $next)
    {
        // Solo validar en solicitudes POST, PUT o DELETE que modifican el estado
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $sessionToken = Session::get('csrf_token');
            
            // Obtener el token desde el cuerpo del formulario o desde las cabeceras HTTP
            $inputToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

            if (empty($sessionToken) || empty($inputToken) || !hash_equals($sessionToken, $inputToken)) {
                if ($request->getMethod() === 'POST' || strpos($request->getUri(), '/api/') !== false) {
                    $response->error("Error de seguridad: Token de validación CSRF inválido o expirado.", 403);
                } else {
                    $response->setStatusCode(403);
                    die("Error 403: Solicitud rechazada por validación de seguridad CSRF.");
                }
                return;
            }
        }

        // Continuar con la petición
        $next();
    }
}
