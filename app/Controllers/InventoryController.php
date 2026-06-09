<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Asset;
use PDO;

class InventoryController extends Controller
{
    private Asset $assetModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->assetModel = new Asset();
    }

    /**
     * Mostrar el listado general de bienes y su estado de verificación física
     */
    public function index()
    {
        $this->authorize('INVENTORY_VIEW');

        $db = Database::getConnection();
        
        // Consulta uniendo todos los bienes con sus detalles de verificación física si existen
        $sql = "
            SELECT a.id, a.custom_code, a.type, a.brand, a.model, a.asset_status as original_status,
                   CONCAT(r.names, ' ', r.surnames) as responsible_name,
                   o.name as office_name,
                   l.name as location_name,
                   v.id as verification_id,
                   v.found,
                   v.asset_status as verified_status,
                   v.observations,
                   v.verified_at,
                   v.gps_lat,
                   v.gps_lng,
                   u.full_name as verified_by_name
            FROM `assets` a
            JOIN `responsibles` r ON a.responsible_id = r.id
            JOIN `offices` o ON a.office_id = o.id
            JOIN `locations` l ON a.location_id = l.id
            LEFT JOIN `inventory_verifications` v ON a.id = v.asset_id
            LEFT JOIN `users` u ON v.verified_by = u.id
            WHERE a.deleted_at IS NULL
            ORDER BY a.custom_code ASC
        ";
        
        $stmt = $db->query($sql);
        $inventoryList = $stmt->fetchAll();

        $this->render('inventories/index', [
            'title' => 'Inventario Físico de Bienes',
            'inventoryList' => $inventoryList
        ]);
    }

    /**
     * API AJAX: Obtener el detalle de verificación de un bien patrimonial
     */
    public function jsonDetail(array $params)
    {
        $this->authorize('INVENTORY_VIEW');
        
        $assetId = (int)$params['id'];
        
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT v.*, a.custom_code, a.type, a.brand, a.model 
            FROM `assets` a 
            LEFT JOIN `inventory_verifications` v ON a.id = v.asset_id 
            WHERE a.id = :asset_id AND a.deleted_at IS NULL
        ");
        $stmt->execute(['asset_id' => $assetId]);
        $detail = $stmt->fetch();

        if (!$detail) {
            $this->response->error("El bien patrimonial no existe.", 404);
            return;
        }

        $this->response->success("Detalle obtenido", $detail);
    }

    /**
     * API AJAX: Guardar o actualizar la verificación física del bien
     */
    public function ajaxSave(array $params)
    {
        $this->authorize('INVENTORY_EDIT');

        $assetId      = (int)$params['id'];
        $found        = (int)$this->request->input('found', 1);
        $assetStatus  = $this->request->input('asset_status', 'Bueno');
        $observations = $this->request->input('observations', '');
        $lat          = $this->request->input('gps_lat', null);
        $lng          = $this->request->input('gps_lng', null);

        // Validar que el bien exista
        $asset = $this->assetModel->find($assetId);
        if (!$asset) {
            $this->response->error("El bien patrimonial no existe.", 404);
            return;
        }

        $db = Database::getConnection();
        
        // Verificar si ya existe registro de verificación (Upsert)
        $stmtCheck = $db->prepare("SELECT id FROM `inventory_verifications` WHERE `asset_id` = :asset_id");
        $stmtCheck->execute(['asset_id' => $assetId]);
        $verificationId = $stmtCheck->fetchColumn();

        if ($verificationId) {
            // Actualizar verificación
            $stmtUpd = $db->prepare("
                UPDATE `inventory_verifications` 
                SET `found` = :found, `asset_status` = :status, `observations` = :obs, `verified_by` = :user_id, `verified_at` = NOW(), `gps_lat` = :lat, `gps_lng` = :lng
                WHERE `id` = :id
            ");
            $result = $stmtUpd->execute([
                'found'   => $found,
                'status'  => $assetStatus,
                'obs'     => $observations,
                'user_id' => Session::get('user_id'),
                'lat'     => !empty($lat) ? $lat : null,
                'lng'     => !empty($lng) ? $lng : null,
                'id'      => $verificationId
            ]);
        } else {
            // Registrar verificación nueva
            $stmtIns = $db->prepare("
                INSERT INTO `inventory_verifications` (`asset_id`, `found`, `asset_status`, `observations`, `verified_by`, `gps_lat`, `gps_lng`) 
                VALUES (:asset_id, :found, :status, :obs, :user_id, :lat, :lng)
            ");
            $result = $stmtIns->execute([
                'asset_id' => $assetId,
                'found'    => $found,
                'status'   => $assetStatus,
                'obs'      => $observations,
                'user_id'  => Session::get('user_id'),
                'lat'      => !empty($lat) ? $lat : null,
                'lng'      => !empty($lng) ? $lng : null
            ]);
        }

        if ($result) {
            // Si el bien fue encontrado y el estado cambió, actualizar el estado físico en la ficha del bien
            if ($found === 1 && $asset['asset_status'] !== $assetStatus) {
                $this->assetModel->update($assetId, [
                    'asset_status' => $assetStatus
                ]);
            }
            
            // Log de auditoría manual
            $db->prepare("
                INSERT INTO `audit_logs` (`user_id`, `action`, `module`, `affected_table`, `affected_record_id`, `ip_address`, `user_agent`, `details`) 
                VALUES (:user_id, 'VERIFY_ASSET', 'Inventory', 'inventory_verifications', :record_id, :ip, :ua, :details)
            ")->execute([
                'user_id'   => Session::get('user_id'),
                'record_id' => $assetId,
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'ua'        => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255),
                'details'   => json_encode(['found' => $found, 'status' => $assetStatus])
            ]);

            $this->response->success("Inspección física del bien registrada correctamente.");
        } else {
            $this->response->error("Ocurrió un error al guardar los datos de verificación.");
        }
    }

    /**
     * API AJAX: Resetear (eliminar) la verificación física de un bien
     */
    public function ajaxReset(array $params)
    {
        $this->authorize('INVENTORY_EDIT');

        $assetId = (int)$params['id'];

        $db = Database::getConnection();
        
        $stmtDel = $db->prepare("DELETE FROM `inventory_verifications` WHERE `asset_id` = :asset_id");
        $result = $stmtDel->execute(['asset_id' => $assetId]);

        if ($result) {
            // Registrar en auditoría
            $db->prepare("
                INSERT INTO `audit_logs` (`user_id`, `action`, `module`, `affected_table`, `affected_record_id`, `ip_address`, `user_agent`) 
                VALUES (:user_id, 'RESET_VERIFY', 'Inventory', 'inventory_verifications', :record_id, :ip, :ua)
            ")->execute([
                'user_id'   => Session::get('user_id'),
                'record_id' => $assetId,
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'ua'        => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255)
            ]);

            $this->response->success("Se ha restablecido el estado de verificación física del bien a 'Pendiente'.");
        } else {
            $this->response->error("No se pudo restablecer la verificación física.");
        }
    }
}
