<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Role extends Model
{
    protected string $table = 'roles';

    /**
     * Obtener los IDs de permisos asociados a un rol específico
     */
    public function getPermissionIds(int $roleId): array
    {
        $sql = "SELECT permission_id FROM `role_permissions` WHERE role_id = :role_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Actualizar la asignación de permisos para un rol (Matriz RBAC)
     */
    public function updatePermissions(int $roleId, array $permissionIds): bool
    {
        try {
            $this->db->beginTransaction();

            // Eliminar asignaciones anteriores
            $sqlDelete = "DELETE FROM `role_permissions` WHERE `role_id` = :role_id";
            $stmtDel = $this->db->prepare($sqlDelete);
            $stmtDel->execute(['role_id' => $roleId]);

            // Insertar nuevas asignaciones si existen
            if (!empty($permissionIds)) {
                $sqlInsert = "INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (:role_id, :permission_id)";
                $stmtIns = $this->db->prepare($sqlInsert);
                
                foreach ($permissionIds as $permId) {
                    $stmtIns->execute([
                        'role_id' => $roleId,
                        'permission_id' => (int)$permId
                    ]);
                }
            }

            $this->db->commit();
            
            // Forzar auditoría manual de cambio de permisos
            $this->logActivity('UPDATE_PERMISSIONS', $roleId, null, ['permissions' => $permissionIds]);
            
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            if (APP_ENV === 'development') {
                die("Error al actualizar permisos de rol: " . $e->getMessage());
            }
            return false;
        }
    }
}
