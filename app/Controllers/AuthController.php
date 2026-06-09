<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    /**
     * Mostrar vista de inicio de sesión
     */
    public function login()
    {
        if (Session::has('user_id') && Session::get('app_uid') === 'invent_patrimonio') {
            $this->response->redirect('/');
            return;
        }

        $error = Session::getFlash('error');
        $success = Session::getFlash('success');

        $this->render('auth/login', [
            'title' => 'Iniciar Sesión',
            'error' => $error,
            'success' => $success
        ], 'auth');
    }

    /**
     * Procesar solicitud de inicio de sesión
     */
    public function loginPost()
    {
        $username = $this->request->input('username', '');
        $password = $this->request->input('password', '');

        if (empty($username) || empty($password)) {
            Session::setFlash('error', 'Por favor, ingrese su usuario y contraseña.');
            $this->response->redirect('/login');
            return;
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            Session::setFlash('error', 'Credenciales de acceso incorrectas.');
            $this->response->redirect('/login');
            return;
        }

        // Validar si la cuenta está bloqueada temporalmente (status = 2)
        if ($user['status'] == 2) {
            $currentTime = time();
            $blockedUntil = strtotime($user['blocked_until']);

            if ($blockedUntil > $currentTime) {
                $timeDiff = ceil(($blockedUntil - $currentTime) / 60);
                Session::setFlash('error', "Cuenta bloqueada temporalmente. Intente en {$timeDiff} minutos.");
                $this->response->redirect('/login');
                return;
            } else {
                // El tiempo de bloqueo ya expiró, desbloquear
                $this->userModel->resetAttempts($user['id']);
                $user['status'] = 1;
            }
        }

        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            // Reiniciar intentos fallidos tras inicio exitoso
            $this->userModel->resetAttempts($user['id']);
            
            // Obtener los permisos asociados al rol
            $permissions = $this->userModel->getPermissions($user['role_id']);

            // Guardar variables de sesión
            Session::set('user_id', $user['id']);
            Session::set('app_uid', 'invent_patrimonio');
            Session::set('user_name', $user['username']);
            Session::set('user_fullname', $user['full_name']);
            Session::set('user_role_id', $user['role_id']);
            Session::set('user_role_name', $user['role_name']);
            Session::set('user_permissions', $permissions);
            Session::set('last_activity', time());

            // Actualizar la última fecha de login
            $this->userModel->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);

            $this->response->redirect('/');
        } else {
            // Incrementar los intentos fallidos
            $this->userModel->incrementAttempts($user['id'], $user['login_attempts']);
            
            $attemptsLeft = 5 - ($user['login_attempts'] + 1);
            if ($attemptsLeft <= 0) {
                Session::setFlash('error', 'Su cuenta ha sido bloqueada temporalmente por 10 minutos debido a demasiados intentos fallidos.');
            } else {
                Session::setFlash('error', "Credenciales incorrectas. Le quedan {$attemptsLeft} intentos.");
            }
            $this->response->redirect('/login');
        }
    }

    /**
     * Cerrar sesión del usuario
     */
    public function logout()
    {
        $reason = $this->request->input('reason', '');
        Session::destroy();
        Session::init();
        if ($reason === 'timeout') {
            Session::setFlash('error', 'Su sesión ha expirado por inactividad. Por favor, ingrese de nuevo.');
        } else {
            Session::setFlash('success', 'Sesión cerrada correctamente.');
        }
        $this->response->redirect('/login');
    }

    /**
     * Mostrar página 403 - Acceso Prohibido
     */
    public function forbidden()
    {
        $this->render('errors/403', [
            'title' => 'Acceso Denegado'
        ], 'auth');
    }
}
