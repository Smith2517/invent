<?php

namespace App\Models;

use App\Core\Model;

class Subgroup extends Model
{
    protected string $table = 'subgroups';

    /**
     * Obtener subgrupos por su ID de grupo principal
     */
    public function getByGroupId(int $groupId): array
    {
        $sql = "SELECT * FROM `subgroups` WHERE `group_id` = :group_id AND `deleted_at` IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll();
    }
}
