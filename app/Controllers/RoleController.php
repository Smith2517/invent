<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    private Role $roleModel;
    private Permission $permissionModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    /**
     * Listar todos los roles
     */
    public function index()
    {
        $this->authorize('ROLE_VIEW');
        
        $roles = $this->roleModel->all();
        
        $this->render('roles/index', [
            'title' => 'Gestión de Roles',
            'roles' => $roles
        ]);
    }

    /**
     * API AJAX: Detalle de un rol y su matriz de permisos
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ROLE_VIEW');
        
        $id = (int)$params['id'];
        $role = $this->roleModel->find($id);

        if (!$role) {
            $this->response->error("El rol solicitado no existe.", 404);
            return;
        }

        // Obtener permisos agrupados por módulo
        $permissionsByModule = $this->permissionModel->getGroupedByModule();
        
        // Obtener IDs de permisos asignados a este rol
        $assignedPermissionIds = $this->roleModel->getPermissionIds($id);

        $this->response->success("Detalle del rol obtenido", [
            'role' => $role,
            'permissionsByModule' => $permissionsByModule,
            'assignedPermissionIds' => $assignedPermissionIds
        ]);
    }

    /**
     * API AJAX: Procesar actualización de permisos del rol
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ROLE_EDIT');
        
        $id = (int)$params['id'];
        $role = $this->roleModel->find($id);

        if (!$role) {
            $this->response->error("El rol solicitado no existe.", 404);
            return;
        }

        // Capturar los IDs de permisos enviados
        $permissionIds = $this->request->input('permissions', []);
        
        // Sanitizar y validar los IDs
        $cleanPermissionIds = array_map('intval', $permissionIds);

        if ($this->roleModel->updatePermissions($id, $cleanPermissionIds)) {
            $this->response->success("Matriz de permisos del rol actualizada correctamente.");
        } else {
            $this->response->error("Ocurrió un error al intentar actualizar los permisos.");
        }
    }
}
