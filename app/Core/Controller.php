<?php

namespace App\Core;

abstract class Controller
{
    protected Request $request;
    protected Response $response;
    protected View $view;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = new View();
    }

    /**
     * Renderizar una vista desde el controlador
     */
    protected function render(string $view, array $data = [], string $layout = 'main')
    {
        // Pasar el token CSRF y los datos del usuario en sesión a todas las vistas por defecto
        $defaultData = [
            'csrf_token' => Session::csrfToken(),
            'currentUser' => Session::get('user_name'),
            'currentUserFullName' => Session::get('user_fullname'),
            'currentUserRole' => Session::get('user_role_name'),
            'userPermissions' => Session::get('user_permissions', [])
        ];
        
        $mergedData = array_merge($defaultData, $data);
        $this->view->render($view, $mergedData, $layout);
    }

    /**
     * Retornar una respuesta JSON rápida
     */
    protected function json(array $data, int $statusCode = 200)
    {
        $this->response->json($data, $statusCode);
    }

    /**
     * Validar si el usuario en sesión cuenta con un permiso específico
     */
    protected function checkPermission(string $permissionCode): bool
    {
        $permissions = Session::get('user_permissions', []);
        return in_array($permissionCode, $permissions);
    }

    /**
     * Validar permiso o abortar la petición con código 403
     */
    protected function authorize(string $permissionCode)
    {
        if (!$this->checkPermission($permissionCode)) {
            if ($this->request->getMethod() === 'POST' || strpos($this->request->getUri(), '/api/') !== false) {
                $this->response->error("No cuenta con permisos para realizar esta acción.", 403);
            } else {
                $this->response->redirect('/forbidden');
            }
        }
    }
}
