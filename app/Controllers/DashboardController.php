<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DashboardController extends Controller
{
    /**
     * Renderizar el panel de control principal
     */
    public function index()
    {
        $db = Database::getConnection();

        // 1. Contador de Bienes Totales
        $stmt = $db->query("SELECT COUNT(*) FROM `assets` WHERE `deleted_at` IS NULL");
        $totalAssets = $stmt->fetchColumn();

        // 2. Contador de Usuarios Activos
        $stmt = $db->query("SELECT COUNT(*) FROM `users` WHERE `deleted_at` IS NULL");
        $totalUsers = $stmt->fetchColumn();

        // 3. Contador de Responsables Custodios
        $stmt = $db->query("SELECT COUNT(*) FROM `responsibles` WHERE `deleted_at` IS NULL");
        $totalResponsibles = $stmt->fetchColumn();

        // 4. Bienes Verificados Físicamente
        $stmt = $db->query("SELECT COUNT(*) FROM `inventory_verifications` WHERE `found` = 1");
        $verifiedAssets = $stmt->fetchColumn();

        // 5. Bienes por Estado Físico (Bueno, Regular, Malo, Chatarra)
        $stmt = $db->query("
            SELECT `asset_status` as label, COUNT(*) as count 
            FROM `assets` 
            WHERE `deleted_at` IS NULL 
            GROUP BY `asset_status`
        ");
        $assetsByStatus = $stmt->fetchAll();

        // 6. Bienes por Grupo Genérico (Top 5 con mayor volumen)
        $stmt = $db->query("
            SELECT g.description as label, COUNT(a.id) as count 
            FROM `assets` a
            JOIN `subgroups` s ON a.subgroup_id = s.id
            JOIN `groups` g ON s.group_id = g.id
            WHERE a.deleted_at IS NULL
            GROUP BY g.id
            ORDER BY count DESC
            LIMIT 5
        ");
        $assetsByGroup = $stmt->fetchAll();

        // 7. Bienes por Oficina / Área (Top 5 con mayor volumen)
        $stmt = $db->query("
            SELECT o.name as label, COUNT(a.id) as count 
            FROM `assets` a
            JOIN `offices` o ON a.office_id = o.id
            WHERE a.deleted_at IS NULL
            GROUP BY o.id
            ORDER BY count DESC
            LIMIT 5
        ");
        $assetsByOffice = $stmt->fetchAll();

        // 8. Altas mensuales de bienes en el último año
        $stmt = $db->query("
            SELECT MONTHNAME(`entry_date`) as month, COUNT(*) as count 
            FROM `assets` 
            WHERE `deleted_at` IS NULL AND `entry_date` >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
            GROUP BY MONTH(`entry_date`)
            ORDER BY `entry_date`
        ");
        $monthlyEntries = $stmt->fetchAll();

        $this->render('dashboard/index', [
            'title' => 'Dashboard Analítico',
            'stats' => [
                'total_assets' => $totalAssets,
                'total_users' => $totalUsers,
                'total_responsibles' => $totalResponsibles,
                'verified_assets' => $verifiedAssets
            ],
            'assetsByStatus' => $assetsByStatus,
            'assetsByGroup' => $assetsByGroup,
            'assetsByOffice' => $assetsByOffice,
            'monthlyEntries' => $monthlyEntries
        ]);
    }
}
