<?php

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected string $table = 'permissions';
    protected bool $useSoftDelete = false;

    /**
     * Obtener todos los permisos agrupados por módulo
     */
    public function getGroupedByModule(): array
    {
        $sql = "SELECT * FROM `permissions` ORDER BY `module`, `code`";
        $stmt = $this->db->query($sql);
        $permissions = $stmt->fetchAll();

        $grouped = [];
        foreach ($permissions as $p) {
            $grouped[$p['module']][] = $p;
        }

        return $grouped;
    }
}
