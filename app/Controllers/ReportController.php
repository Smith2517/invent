<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Office;
use App\Models\Location;
use App\Models\Responsible;
use App\Models\Group;
use App\Models\FundingSource;

class ReportController extends Controller
{
    private Office $officeModel;
    private Location $locationModel;
    private Responsible $responsibleModel;
    private Group $groupModel;
    private FundingSource $fundingSourceModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->officeModel = new Office();
        $this->locationModel = new Location();
        $this->responsibleModel = new Responsible();
        $this->groupModel = new Group();
        $this->fundingSourceModel = new FundingSource();
    }

    /**
     * Vista principal del generador de reportes con filtros
     */
    public function index()
    {
        $this->authorize('REPORT_VIEW');

        $offices = $this->officeModel->all();
        $locations = $this->locationModel->all();
        $responsibles = $this->responsibleModel->all();
        $groups = $this->groupModel->all();
        $fundingSources = $this->fundingSourceModel->all();

        // Obtener todos los años únicos en los que se ingresaron bienes
        $db = \App\Core\Database::getConnection();
        $stmt = $db->query("SELECT DISTINCT YEAR(entry_date) as year FROM `assets` WHERE `deleted_at` IS NULL ORDER BY year DESC");
        $years = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->render('reports/index', [
            'title' => 'Reportes Patrimoniales Analíticos',
            'offices' => $offices,
            'locations' => $locations,
            'responsibles' => $responsibles,
            'groups' => $groups,
            'fundingSources' => $fundingSources,
            'years' => $years
        ]);
    }

    /**
     * API AJAX: Buscar bienes patrimoniales y calcular estadísticas dinámicas filtradas
     */
    public function ajaxSearch()
    {
        $this->authorize('REPORT_VIEW');

        $searchText = trim($this->request->input('search_text', ''));
        $year = $this->request->input('year', '');
        $dateFrom = $this->request->input('date_from', '');
        $dateTo = $this->request->input('date_to', '');
        $officeId = $this->request->input('office_id', '');
        $locationId = $this->request->input('location_id', '');
        $responsibleId = $this->request->input('responsible_id', '');
        $groupId = $this->request->input('group_id', '');
        $fundingSourceId = $this->request->input('funding_source_id', '');
        $assetStatus = $this->request->input('asset_status', '');

        $db = \App\Core\Database::getConnection();

        $sql = "SELECT a.*, 
                       s.description as subgroup_name, 
                       g.description as group_name,
                       o.name as office_name,
                       l.name as location_name,
                       CONCAT(r.names, ' ', r.surnames) as responsible_name,
                       f.name as funding_name
                FROM `assets` a
                JOIN `subgroups` s ON a.subgroup_id = s.id
                JOIN `groups` g ON s.group_id = g.id
                JOIN `responsibles` r ON a.responsible_id = r.id
                JOIN `offices` o ON a.office_id = o.id
                JOIN `locations` l ON a.location_id = l.id
                JOIN `funding_sources` f ON a.funding_source_id = f.id
                WHERE a.deleted_at IS NULL";

        $params = [];

        if (!empty($searchText)) {
            $sql .= " AND (a.type LIKE :search OR a.brand LIKE :search OR a.model LIKE :search OR a.serial_number LIKE :search OR a.custom_code LIKE :search OR a.characteristics LIKE :search)";
            $params['search'] = '%' . $searchText . '%';
        }

        if (!empty($year)) {
            $sql .= " AND YEAR(a.entry_date) = :year";
            $params['year'] = (int)$year;
        }

        if (!empty($dateFrom)) {
            $sql .= " AND a.entry_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $sql .= " AND a.entry_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        if (!empty($officeId)) {
            $sql .= " AND a.office_id = :office_id";
            $params['office_id'] = (int)$officeId;
        }

        if (!empty($locationId)) {
            $sql .= " AND a.location_id = :location_id";
            $params['location_id'] = (int)$locationId;
        }

        if (!empty($responsibleId)) {
            $sql .= " AND a.responsible_id = :responsible_id";
            $params['responsible_id'] = (int)$responsibleId;
        }

        if (!empty($groupId)) {
            $sql .= " AND s.group_id = :group_id";
            $params['group_id'] = (int)$groupId;
        }

        if (!empty($fundingSourceId)) {
            $sql .= " AND a.funding_source_id = :funding_source_id";
            $params['funding_source_id'] = (int)$fundingSourceId;
        }

        if (!empty($assetStatus)) {
            $sql .= " AND a.asset_status = :asset_status";
            $params['asset_status'] = $assetStatus;
        }

        // Ordenar del más reciente al más antiguo de forma predeterminada
        $sql .= " ORDER BY a.entry_date DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $assets = $stmt->fetchAll();

        // -----------------------------------------------------
        // Cálculo de Estadísticas Dinámicas sobre el Subconjunto
        // -----------------------------------------------------
        $totalCount = count($assets);
        $statusCounts = ['Bueno' => 0, 'Regular' => 0, 'Malo' => 0, 'Chatarra' => 0];
        $yearCounts = [];
        $oldestAsset = null;
        $newestAsset = null;

        if ($totalCount > 0) {
            // Dado que el SQL está ordenado por entry_date DESC:
            $newestAsset = $assets[0];
            $oldestAsset = $assets[$totalCount - 1];

            foreach ($assets as $asset) {
                // Estado Físico
                $status = $asset['asset_status'];
                if (isset($statusCounts[$status])) {
                    $statusCounts[$status]++;
                }

                // Por Años
                $yearVal = date('Y', strtotime($asset['entry_date']));
                if (!isset($yearCounts[$yearVal])) {
                    $yearCounts[$yearVal] = 0;
                }
                $yearCounts[$yearVal]++;
            }

            // Ordenar distribución de años descendente
            krsort($yearCounts);
        }

        $this->response->success("Búsqueda analítica completada", [
            'assets' => $assets,
            'stats' => [
                'total_count' => $totalCount,
                'status_counts' => $statusCounts,
                'year_counts' => $yearCounts,
                'oldest_asset' => $oldestAsset,
                'newest_asset' => $newestAsset
            ]
        ]);
    }
}
