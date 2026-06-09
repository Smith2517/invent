<?php

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, Response $response, array $permissions, callable $next)
    {
        // Si no existe el usuario o la sesión es de otra app local, redirigir al login
        if (!Session::has('user_id') || Session::get('app_uid') !== 'invent_patrimonio') {
            Session::destroy();
            Session::init();
            Session::setFlash('error', 'Por favor, inicie sesión para acceder al sistema.');
            $response->redirect('/login');
            return;
        }

        // Verificar el tiempo de inactividad de la sesión
        $lastActivity = Session::get('last_activity', 0);
        $currentTime = time();

        if ($lastActivity > 0 && ($currentTime - $lastActivity) > SESSION_TIMEOUT) {
            // La sesión ha expirado
            Session::destroy();
            Session::init(); // Inicializar una nueva sesión para almacenar el mensaje flash
            Session::setFlash('error', 'Su sesión ha expirado por inactividad. Por favor, ingrese de nuevo.');
            $response->redirect('/login');
            return;
        }

        // Actualizar la marca de tiempo de última actividad
        Session::set('last_activity', $currentTime);

        // Continuar con la petición
        $next();
    }
}
