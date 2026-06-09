<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware extends Middleware
{
    public function handle(Request $request, Response $response, array $permissions, callable $next)
    {
        $userPerms = Session::get('user_permissions', []);

        // Validar que el usuario posea los permisos indicados en la ruta
        foreach ($permissions as $requiredPermission) {
            if (!in_array($requiredPermission, $userPerms)) {
                // Denegar el acceso si no cuenta con el permiso requerido
                if ($request->getMethod() === 'POST' || strpos($request->getUri(), '/api/') !== false) {
                    $response->error("No cuenta con autorización para realizar esta operación (Permiso requerido: {$requiredPermission}).", 403);
                } else {
                    // Cargar vista de prohibido
                    $response->redirect('/forbidden');
                }
                return;
            }
        }

        // Si cumple con los permisos, continuar la cadena de ejecución
        $next();
    }
}
