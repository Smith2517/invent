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

        // 6. Bienes por Grupo Presupuestario
        $stmt = $db->query("
            SELECT g.description as label, COUNT(a.id) as count 
            FROM `assets` a
            JOIN `subgroups` s ON a.subgroup_id = s.id
            JOIN `groups` g ON s.group_id = g.id
            WHERE a.deleted_at IS NULL
            GROUP BY g.id
            LIMIT 5
        ");
        $assetsByGroup = $stmt->fetchAll();

        // 7. Altas mensuales de bienes en el último año
        $stmt = $db->query("
            SELECT MONTHNAME(`entry_date`) as month, COUNT(*) as count 
            FROM `assets` 
            WHERE `deleted_at` IS NULL AND `entry_date` >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
            GROUP BY MONTH(`entry_date`)
            ORDER BY `entry_date`
        ");
        $monthlyEntries = $stmt->fetchAll();

        // 8. Registro de Actividades Recientes (Auditoría)
        $stmt = $db->query("
            SELECT l.*, u.full_name as user_fullname 
            FROM `audit_logs` l
            LEFT JOIN `users` u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT 5
        ");
        $recentLogs = $stmt->fetchAll();

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
            'monthlyEntries' => $monthlyEntries,
            'recentLogs' => $recentLogs
        ]);
    }
}
