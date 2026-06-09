<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    private User $userModel;
    private Role $roleModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        $this->authorize('USER_VIEW');
        
        $db = \App\Core\Database::getConnection();
        $stmt = $db->query("
            SELECT u.*, r.name as role_name 
            FROM `users` u 
            JOIN `roles` r ON u.role_id = r.id 
            WHERE u.deleted_at IS NULL
            ORDER BY u.created_at DESC
        ");
        $users = $stmt->fetchAll();
        $roles = $this->roleModel->all();

        $this->render('users/index', [
            'title' => 'Gestión de Usuarios',
            'users' => $users,
            'roles' => $roles
        ]);
    }

    /**
     * API AJAX: Detalle de un usuario
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('USER_VIEW');
        $id = (int)$params['id'];

        $user = $this->userModel->find($id);

        if (!$user) {
            $this->response->error("El usuario no existe.", 404);
            return;
        }

        // Ocultar password por seguridad
        unset($user['password']);

        $this->response->success("Detalle de usuario obtenido", $user);
    }

    /**
     * API AJAX: Guardar nuevo usuario
     */
    public function ajaxSave()
    {
        $this->authorize('USER_CREATE');

        $username  = trim($this->request->input('username', ''));
        $email     = trim($this->request->input('email', ''));
        $password  = $this->request->input('password', '');
        $fullName  = trim($this->request->input('full_name', ''));
        $roleId    = (int)$this->request->input('role_id', 0);
        $status    = (int)$this->request->input('status', 1);

        if (empty($username) || empty($email) || empty($password) || empty($fullName) || $roleId <= 0) {
            $this->response->error("Por favor, complete todos los campos obligatorios.");
            return;
        }

        $db = \App\Core\Database::getConnection();
        
        // Validar username único
        $stmt = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = :username AND `deleted_at` IS NULL");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El nombre de usuario ya se encuentra registrado.");
            return;
        }

        // Validar email único
        $stmt = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `email` = :email AND `deleted_at` IS NULL");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El correo electrónico ya se encuentra registrado.");
            return;
        }

        // Crear el usuario
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        $userId = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $passwordHash,
            'full_name' => $fullName,
            'role_id' => $roleId,
            'status' => $status
        ]);

        if ($userId > 0) {
            $this->response->success("Usuario registrado correctamente.");
        } else {
            $this->response->error("No se pudo registrar el usuario.");
        }
    }

    /**
     * API AJAX: Actualizar usuario existente
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('USER_EDIT');
        $id = (int)$params['id'];

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->response->error("El usuario no existe.", 404);
            return;
        }

        $username  = trim($this->request->input('username', ''));
        $email     = trim($this->request->input('email', ''));
        $password  = $this->request->input('password', '');
        $fullName  = trim($this->request->input('full_name', ''));
        $roleId    = (int)$this->request->input('role_id', 0);
        $status    = (int)$this->request->input('status', 1);

        if (empty($username) || empty($email) || empty($fullName) || $roleId <= 0) {
            $this->response->error("Por favor, complete todos los campos obligatorios.");
            return;
        }

        $db = \App\Core\Database::getConnection();
        
        // Validar username único excluyendo al actual
        $stmt = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = :username AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['username' => $username, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El nombre de usuario ya está registrado por otro usuario.");
            return;
        }

        // Validar email único excluyendo al actual
        $stmt = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `email` = :email AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['email' => $email, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El correo electrónico ya está registrado por otro usuario.");
            return;
        }

        $updateData = [
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'role_id' => $roleId,
            'status' => $status
        ];

        // Hashear password solo si se ingresó uno nuevo
        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $updated = $this->userModel->update($id, $updateData);

        if ($updated) {
            $this->response->success("Usuario actualizado correctamente.");
        } else {
            $this->response->error("No se detectaron cambios o no se pudo actualizar.");
        }
    }

    /**
     * API AJAX: Eliminar usuario (Baja lógica con validación de auto-eliminación)
     */
    public function ajaxDelete(array $params)
    {
        $this->authorize('USER_DELETE');
        $id = (int)$params['id'];

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->response->error("El usuario no existe.", 404);
            return;
        }

        // Validación de Seguridad: Evitar que el usuario se elimine a sí mismo
        $currentUserId = (int)Session::get('user_id');
        if ($id === $currentUserId) {
            $this->response->error("Acción denegada: No puede eliminar su propia cuenta de usuario en sesión.");
            return;
        }

        if ($this->userModel->delete($id)) {
            $this->response->success("Usuario eliminado correctamente.");
        } else {
            $this->response->error("No se pudo eliminar el usuario.");
        }
    }
}
