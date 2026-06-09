<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Asset extends Model
{
    protected string $table = 'assets';

    /**
     * Obtener listado de bienes con relaciones completas
     */
    public function allWithRelations(): array
    {
        $sql = "SELECT a.*, 
                       s.description as subgroup_name, 
                       g.description as group_name,
                       CONCAT(r.names, ' ', r.surnames) as responsible_name,
                       o.name as office_name,
                       l.name as location_name,
                       f.name as funding_name
                FROM `assets` a
                JOIN `subgroups` s ON a.subgroup_id = s.id
                JOIN `groups` g ON s.group_id = g.id
                JOIN `responsibles` r ON a.responsible_id = r.id
                JOIN `offices` o ON a.office_id = o.id
                JOIN `locations` l ON a.location_id = l.id
                JOIN `funding_sources` f ON a.funding_source_id = f.id
                WHERE a.deleted_at IS NULL
                ORDER BY a.created_at DESC";
                
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Buscar un bien específico con sus relaciones completas
     */
    public function findWithRelations(int $id)
    {
        $sql = "SELECT a.*, 
                       s.description as subgroup_name, 
                       s.code as subgroup_code,
                       g.description as group_name,
                       g.code as group_code,
                       g.id as group_id,
                       r.names as responsible_names,
                       r.surnames as responsible_surnames,
                       r.position as responsible_position,
                       o.name as office_name,
                       l.name as location_name,
                       l.address as location_address,
                       f.name as funding_name
                FROM `assets` a
                JOIN `subgroups` s ON a.subgroup_id = s.id
                JOIN `groups` g ON s.group_id = g.id
                JOIN `responsibles` r ON a.responsible_id = r.id
                JOIN `offices` o ON a.office_id = o.id
                JOIN `locations` l ON a.location_id = l.id
                JOIN `funding_sources` f ON a.funding_source_id = f.id
                WHERE a.id = :id AND a.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtener fotos adicionales de un bien
     */
    public function getAdditionalPhotos(int $assetId): array
    {
        $sql = "SELECT * FROM `asset_photos` WHERE `asset_id` = :asset_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    /**
     * Guardar fotos adicionales asociadas a un bien
     */
    public function savePhotos(int $assetId, array $photoPaths)
    {
        $sql = "INSERT INTO `asset_photos` (`asset_id`, `photo_path`) VALUES (:asset_id, :photo_path)";
        $stmt = $this->db->prepare($sql);
        foreach ($photoPaths as $path) {
            $stmt->execute([
                'asset_id' => $assetId,
                'photo_path' => $path
            ]);
        }
    }
}
