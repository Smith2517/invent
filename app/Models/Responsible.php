<?php

namespace App\Models;

use App\Core\Model;

class Responsible extends Model
{
    protected string $table = 'responsibles';

    /**
     * Listar responsables con el nombre de su unidad orgánica
     */
    public function allWithOffice(): array
    {
        $sql = "SELECT r.*, o.name as office_name 
                FROM `responsibles` r
                JOIN `offices` o ON r.office_id = o.id
                WHERE r.deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
