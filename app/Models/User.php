<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Buscar un usuario por su nombre de usuario (username)
     */
    public function findByUsername(string $username)
    {
        $sql = "SELECT u.*, r.name as role_name 
                FROM `users` u
                JOIN `roles` r ON u.role_id = r.id
                WHERE u.username = :username AND u.deleted_at IS NULL";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Obtener una lista de códigos de permisos asignados a un rol específico
     */
    public function getPermissions(int $roleId): array
    {
        $sql = "SELECT p.code 
                FROM `permissions` p
                JOIN `role_permissions` rp ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Registrar un intento fallido de inicio de sesión
     */
    public function incrementAttempts(int $id, int $currentAttempts): bool
    {
        $attempts = $currentAttempts + 1;
        
        if ($attempts >= 5) {
            // Bloquear por 10 minutos
            $blockedUntil = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $sql = "UPDATE `users` SET `login_attempts` = :attempts, `status` = 2, `blocked_until` = :blocked_until WHERE `id` = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'attempts' => $attempts,
                'blocked_until' => $blockedUntil,
                'id' => $id
            ]);
        } else {
            $sql = "UPDATE `users` SET `login_attempts` = :attempts WHERE `id` = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'attempts' => $attempts,
                'id' => $id
            ]);
        }
    }

    /**
     * Restablecer los intentos fallidos de inicio de sesión tras un acceso exitoso
     */
    public function resetAttempts(int $id): bool
    {
        $sql = "UPDATE `users` SET `login_attempts` = 0, `status` = 1, `blocked_until` = NULL WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
