<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Asset;
use App\Models\Group;
use App\Models\Responsible;
use App\Models\Office;
use App\Models\Location;
use App\Models\FundingSource;

class AssetController extends Controller
{
    private Asset $assetModel;
    private Group $groupModel;
    private Responsible $responsibleModel;
    private Office $officeModel;
    private Location $locationModel;
    private FundingSource $fundingModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->assetModel = new Asset();
        $this->groupModel = new Group();
        $this->responsibleModel = new Responsible();
        $this->officeModel = new Office();
        $this->locationModel = new Location();
        $this->fundingModel = new FundingSource();
    }

    /**
     * Listar todos los bienes patrimoniales
     */
    public function index()
    {
        $this->authorize('ASSET_VIEW');
        
        $assets = $this->assetModel->allWithRelations();
        $groups = $this->groupModel->all();
        $responsibles = $this->responsibleModel->all();
        $offices = $this->officeModel->all();
        $locations = $this->locationModel->all();
        $fundings = $this->fundingModel->all();
        
        $this->render('assets/index', [
            'title' => 'Inventario de Bienes',
            'assets' => $assets,
            'groups' => $groups,
            'responsibles' => $responsibles,
            'offices' => $offices,
            'locations' => $locations,
            'fundings' => $fundings
        ]);
    }

    /**
     * Procesar la eliminación lógica (Soft Delete)
     */
    public function delete(array $params)
    {
        $this->authorize('ASSET_DELETE');
        
        $id = (int)$params['id'];
        $asset = $this->assetModel->find($id);

        if (!$asset) {
            $this->response->error("El bien solicitado no existe.", 404);
            return;
        }

        if ($this->assetModel->delete($id)) {
            // Registrar movimiento de Baja
            $db = \App\Core\Database::getConnection();
            $db->prepare("
                INSERT INTO `movements` (`movement_type`, `asset_id`, `origin_responsible_id`, `origin_office_id`, `origin_location_id`, `movement_date`, `observations`, `created_by`) 
                VALUES ('Baja', :asset_id, :resp_id, :office_id, :loc_id, CURDATE(), 'Baja del sistema por eliminación lógica', :user_id)
            ")->execute([
                'asset_id'  => $id,
                'resp_id'   => $asset['responsible_id'],
                'office_id' => $asset['office_id'],
                'loc_id'    => $asset['location_id'],
                'user_id'   => Session::get('user_id')
            ]);

            $this->response->success("Bien patrimonial eliminado correctamente.");
        } else {
            $this->response->error("No se pudo eliminar el bien.");
        }
    }

    /**
     * Mostrar vista detallada e imprimible del bien con su QR generado en PDF
     */
    public function print(array $params)
    {
        $this->authorize('ASSET_PRINT');

        $id = (int)$params['id'];
        $asset = $this->assetModel->findWithRelations($id);

        if (!$asset) {
            Session::setFlash('error', 'El bien patrimonial solicitado no existe.');
            $this->response->redirect('/bienes');
            return;
        }

        // Asegurar la existencia física del código QR
        if (empty($asset['qr_code']) || !file_exists(ROOT_DIR . $asset['qr_code'])) {
            $qrCodePath = $this->generateAndSaveQr($id, $asset['custom_code']);
            if (!empty($qrCodePath)) {
                $this->assetModel->update($id, ['qr_code' => $qrCodePath]);
                $asset['qr_code'] = $qrCodePath;
            }
        }

        // Importar FPDF
        require_once ROOT_DIR . '/app/ThirdParty/fpdf/fpdf.php';

        // Crear instancia de PDF
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false); // Mantener en una sola página fija
        $pdf->AddPage();

        // Color primario (Azul Flexy: #0d6efd)
        $rPrimary = 13; $gPrimary = 110; $bPrimary = 253;

        // -----------------------------------------------------
        // CABECERA
        // -----------------------------------------------------
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor($rPrimary, $gPrimary, $bPrimary);
        $pdf->Cell(130, 8, $this->cleanString("FICHA DE CONTROL PATRIMONIAL"), 0, 1);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(85, 85, 85);
        $pdf->Cell(130, 5, $this->cleanString("Acta de Asignación y Responsabilidad de Custodia"), 0, 1);
        
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(130, 5, $this->cleanString("Generado el: " . date('d/m/Y H:i:s')), 0, 1);

        // Dibujar Código QR (Top derecha)
        $qrPath = ROOT_DIR . $asset['qr_code'];
        if (file_exists($qrPath)) {
            $pdf->Image($qrPath, 165, 15, 30, 30);
        } else {
            $pdf->Rect(165, 15, 30, 30);
            $pdf->SetXY(165, 28);
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(30, 4, $this->cleanString("Sin QR"), 0, 0, 'C');
        }

        // Línea divisoria decorativa
        $pdf->SetDrawColor($rPrimary, $gPrimary, $bPrimary);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(15, 48, 195, 48);

        // -----------------------------------------------------
        // SECCIÓN 1: DATOS CLAVE E IMAGEN PRINCIPAL
        // -----------------------------------------------------
        $yStart = 53;
        
        // Tabla de datos clave (a la izquierda)
        $pdf->SetXY(15, $yStart);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(40, 7, $this->cleanString("Código Patrimonial:"), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor($rPrimary, $gPrimary, $bPrimary);
        $pdf->Cell(80, 7, $this->cleanString($asset['custom_code']), 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(40, 7, $this->cleanString("Tipo de Activo:"), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(80, 7, $this->cleanString($asset['type']), 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(40, 7, $this->cleanString("Marca / Modelo:"), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(80, 7, $this->cleanString(($asset['brand'] ?? 'S/M') . ' / ' . ($asset['model'] ?? 'S/M')), 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(40, 7, $this->cleanString("Número de Serie:"), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(80, 7, $this->cleanString($asset['serial_number'] ?? 'S/N'), 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(40, 7, $this->cleanString("Estado Físico:"), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        
        $status = $asset['asset_status'];
        if ($status === 'Bueno') {
            $pdf->SetTextColor(40, 167, 69); // verde
        } elseif ($status === 'Regular') {
            $pdf->SetTextColor($rPrimary, $gPrimary, $bPrimary); // azul
        } else {
            $pdf->SetTextColor(220, 53, 69); // rojo
        }
        $pdf->Cell(80, 7, $this->cleanString($status), 0, 1);

        // Fotografía Principal (a la derecha)
        $photoWidth = 55;
        $photoHeight = 35;
        $photoX = 140;
        $photoY = $yStart;
        
        $photoPath = !empty($asset['main_photo']) ? ROOT_DIR . $asset['main_photo'] : '';
        if (!empty($photoPath) && file_exists($photoPath)) {
            // Dibujar borde y foto
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Rect($photoX, $photoY, $photoWidth, $photoHeight);
            $pdf->Image($photoPath, $photoX + 1, $photoY + 1, $photoWidth - 2, $photoHeight - 2);
        } else {
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Rect($photoX, $photoY, $photoWidth, $photoHeight);
            $pdf->SetXY($photoX, $photoY + ($photoHeight / 2) - 3);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell($photoWidth, 6, $this->cleanString("Sin fotografía principal"), 0, 0, 'C');
        }

        // -----------------------------------------------------
        // SECCIÓN 2: DATOS TÉCNICOS Y CLASIFICACIÓN
        // -----------------------------------------------------
        $ySec2 = 93;
        $pdf->SetXY(15, $ySec2);
        $pdf->SetFillColor($rPrimary, $gPrimary, $bPrimary);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(180, 6, $this->cleanString(" 1. DATOS TÉCNICOS Y CLASIFICACIÓN"), 0, 1, 'L', true);
        
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        
        $pdf->SetXY(15, $ySec2 + 8);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Grupo:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(65, 6, $this->cleanString($asset['group_name']), 0, 0);
        
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Subgrupo:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(65, 6, $this->cleanString($asset['subgroup_name']), 0, 1);

        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("F. Ingreso:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(65, 6, date('d/m/Y', strtotime($asset['entry_date'])), 0, 0);
        
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Vida Útil:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(65, 6, $this->cleanString($asset['useful_life'] . " meses"), 0, 1);

        // -----------------------------------------------------
        // SECCIÓN 3: UBICACIÓN Y ASIGNACIÓN PRESUPUESTAL
        // -----------------------------------------------------
        $ySec3 = 119;
        $pdf->SetXY(15, $ySec3);
        $pdf->SetFillColor($rPrimary, $gPrimary, $bPrimary);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(180, 6, $this->cleanString(" 2. UBICACIÓN Y ASIGNACIÓN PRESUPUESTAL"), 0, 1, 'L', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, $ySec3 + 8);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Sede:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(65, 6, $this->cleanString($asset['location_name']), 0, 0);
        
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Oficina / Área:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(65, 6, $this->cleanString($asset['office_name']), 0, 1);

        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Financiamiento:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(155, 6, $this->cleanString($asset['funding_name']), 0, 1);

        // -----------------------------------------------------
        // SECCIÓN 4: PERSONAL DE CUSTODIA RESPONSABLE
        // -----------------------------------------------------
        $ySec4 = 145;
        $pdf->SetXY(15, $ySec4);
        $pdf->SetFillColor($rPrimary, $gPrimary, $bPrimary);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(180, 6, $this->cleanString(" 3. PERSONAL DE CUSTODIA RESPONSABLE"), 0, 1, 'L', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, $ySec4 + 8);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Responsable:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(155, 6, $this->cleanString($asset['responsible_names'] . ' ' . $asset['responsible_surnames']), 0, 1);
        
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 6, $this->cleanString("Cargo:"), 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(155, 6, $this->cleanString($asset['responsible_position'] ?? 'No especificado'), 0, 1);

        // -----------------------------------------------------
        // SECCIÓN 5: CARACTERÍSTICAS Y OBSERVACIONES
        // -----------------------------------------------------
        $ySec5 = 171;
        $pdf->SetXY(15, $ySec5);
        $pdf->SetFillColor($rPrimary, $gPrimary, $bPrimary);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(180, 6, $this->cleanString(" 4. DETALLES PARTICULARES Y OBSERVACIONES"), 0, 1, 'L', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetFillColor(248, 249, 250);
        
        // Caja de texto
        $boxY = $ySec5 + 8;
        $pdf->Rect(15, $boxY, 180, 48, 'DF');
        
        $pdf->SetXY(18, $boxY + 3);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(174, 4, $this->cleanString("Características Particulares:"), 0, 1);
        
        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(85, 85, 85);
        $charText = !empty($asset['characteristics']) ? $asset['characteristics'] : 'Ninguna característica particular.';
        $pdf->MultiCell(174, 4.5, $this->cleanString($charText), 0, 'L');
        
        $pdf->Ln(2);
        
        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(174, 4, $this->cleanString("Observaciones Generales:"), 0, 1);
        
        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(85, 85, 85);
        $obsText = !empty($asset['observations']) ? $asset['observations'] : 'Ninguna observación.';
        $pdf->MultiCell(174, 4.5, $this->cleanString($obsText), 0, 'L');

        // -----------------------------------------------------
        // SECCIÓN 6: BLOQUE DE FIRMAS
        // -----------------------------------------------------
        $yFirmas = 245;
        $pdf->SetXY(15, $yFirmas);
        $pdf->SetDrawColor(100, 100, 100);
        $pdf->SetLineWidth(0.3);
        
        // Firma izquierda
        $pdf->Line(25, $yFirmas + 15, 95, $yFirmas + 15);
        $pdf->SetXY(25, $yFirmas + 17);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(70, 4, $this->cleanString("Firma del Custodio Responsable"), 0, 1, 'C');
        $pdf->SetX(25);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(70, 4, $this->cleanString("DNI / Nombre del Servidor"), 0, 1, 'C');

        // Firma derecha
        $pdf->Line(115, $yFirmas + 15, 185, $yFirmas + 15);
        $pdf->SetXY(115, $yFirmas + 17);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(70, 4, $this->cleanString("Firma de Control Patrimonial"), 0, 1, 'C');
        $pdf->SetX(115);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(70, 4, $this->cleanString("Auditor / Supervisor"), 0, 1, 'C');

        // Output
        $pdf->Output('I', 'ficha_patrimonial_' . $asset['custom_code'] . '.pdf');
        exit;
    }

    /**
     * Mostrar vista de etiqueta de barra/QR imprimible
     */
    public function label(array $params)
    {
        $this->authorize('ASSET_PRINT');
        $id = (int)$params['id'];
        $asset = $this->assetModel->findWithRelations($id);

        if (!$asset) {
            Session::setFlash('error', 'El bien patrimonial solicitado no existe.');
            $this->response->redirect('/bienes');
            return;
        }

        $this->render('assets/label', [
            'title' => 'Etiqueta: ' . htmlspecialchars($asset['custom_code']),
            'asset' => $asset
        ], ''); // Renderizar sin layout
    }

    /**
     * Exportar el listado general de bienes patrimoniales a un archivo CSV (Excel)
     */
    public function export()
    {
        $this->authorize('ASSET_EXPORT');

        $assets = $this->assetModel->allWithRelations();

        $fileName = 'inventario_bienes_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        $output = fopen('php://output', 'w');

        // Escribir el BOM UTF-8 para compatibilidad directa con Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");

        // Cabeceras de las columnas
        fputcsv($output, [
            'Código Patrimonial',
            'Tipo de Activo',
            'Marca',
            'Modelo',
            'Número de Serie',
            'Estado de Conservación',
            'Responsable Custodio',
            'Área / Oficina',
            'Localidad / Sede',
            'Fuente de Financiamiento',
            'Fecha de Ingreso'
        ], ';');

        // Filas de datos
        foreach ($assets as $asset) {
            fputcsv($output, [
                $asset['custom_code'],
                $asset['type'],
                $asset['brand'] ?? 'S/M',
                $asset['model'] ?? 'S/M',
                $asset['serial_number'] ?? 'S/N',
                $asset['asset_status'],
                $asset['responsible_name'],
                $asset['office_name'],
                $asset['location_name'],
                $asset['funding_name'],
                date('d/m/Y', strtotime($asset['entry_date']))
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * API AJAX: Obtener detalle completo de un bien patrimonial en JSON
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ASSET_VIEW');
        $id = (int)$params['id'];
        
        $asset = $this->assetModel->findWithRelations($id);

        if (!$asset) {
            $this->response->error("El bien patrimonial solicitado no existe.", 404);
            return;
        }

        // Asegurar la existencia física del código QR
        if (empty($asset['qr_code']) || !file_exists(ROOT_DIR . $asset['qr_code'])) {
            $qrCodePath = $this->generateAndSaveQr($id, $asset['custom_code']);
            if (!empty($qrCodePath)) {
                $this->assetModel->update($id, ['qr_code' => $qrCodePath]);
                $asset['qr_code'] = $qrCodePath;
            }
        }

        $this->response->success("Detalle obtenido", $asset);
    }

    /**
     * API AJAX: Guardar nuevo bien patrimonial
     */
    public function ajaxSave()
    {
        $this->authorize('ASSET_CREATE');

        $customCode      = $this->request->input('custom_code', '');
        $subgroupId      = (int)$this->request->input('subgroup_id', 0);
        $type            = $this->request->input('type', '');
        $entryDate       = $this->request->input('entry_date', '');
        $acquisitionDate = $this->request->input('acquisition_date', '');
        $deliveryDate    = $this->request->input('delivery_date', '');
        $usefulLife      = (int)$this->request->input('useful_life', 0);
        $assetStatus     = $this->request->input('asset_status', 'Bueno');
        $serialNumber    = $this->request->input('serial_number', '');
        $brand           = $this->request->input('brand', '');
        $model           = $this->request->input('model', '');
        $characteristics = $this->request->input('characteristics', '');
        $responsibleId   = (int)$this->request->input('responsible_id', 0);
        $officeId        = (int)$this->request->input('office_id', 0);
        $locationId      = (int)$this->request->input('location_id', 0);
        $fundingSourceId = (int)$this->request->input('funding_source_id', 0);
        $observations    = $this->request->input('observations', '');

        // Validaciones básicas
        if (empty($customCode) || $subgroupId <= 0 || empty($type) || empty($entryDate) || $responsibleId <= 0 || $officeId <= 0 || $locationId <= 0 || $fundingSourceId <= 0) {
            $this->response->error('Por favor, complete todos los campos obligatorios marcados con (*).');
            return;
        }

        // Validar código patrimonial único
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `assets` WHERE `custom_code` = :code AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $customCode]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error('El código patrimonial ingresado ya está registrado.');
            return;
        }

        // Cargar foto principal
        $mainPhotoPath = null;
        if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === UPLOAD_ERR_OK) {
            $mainPhotoPath = $this->uploadPhoto($_FILES['main_photo']);
        }

        // Insertar el bien
        $assetId = $this->assetModel->create([
            'custom_code'       => $customCode,
            'subgroup_id'       => $subgroupId,
            'type'              => $type,
            'entry_date'        => $entryDate,
            'acquisition_date'  => !empty($acquisitionDate) ? $acquisitionDate : null,
            'delivery_date'     => !empty($deliveryDate) ? $deliveryDate : null,
            'useful_life'       => $usefulLife,
            'asset_status'      => $assetStatus,
            'serial_number'     => !empty($serialNumber) ? $serialNumber : null,
            'brand'             => !empty($brand) ? $brand : null,
            'model'             => !empty($model) ? $model : null,
            'characteristics'   => !empty($characteristics) ? $characteristics : null,
            'responsible_id'    => $responsibleId,
            'office_id'         => $officeId,
            'location_id'       => $locationId,
            'funding_source_id' => $fundingSourceId,
            'observations'      => !empty($observations) ? $observations : null,
            'main_photo'        => $mainPhotoPath,
            'status'            => 1
        ]);

        if ($assetId > 0) {
            // Generar y descargar el código QR
            $qrCodePath = $this->generateAndSaveQr($assetId, $customCode);
            $this->assetModel->update($assetId, [
                'qr_code' => $qrCodePath
            ]);

            // Cargar fotografías adicionales si existen
            if (isset($_FILES['photos'])) {
                $additionalPhotos = $this->uploadMultiplePhotos($_FILES['photos']);
                if (!empty($additionalPhotos)) {
                    $this->assetModel->savePhotos($assetId, $additionalPhotos);
                }
            }

            // Registrar movimiento inicial de alta
            $db->prepare("
                INSERT INTO `movements` (`movement_type`, `asset_id`, `target_responsible_id`, `target_office_id`, `target_location_id`, `movement_date`, `observations`, `created_by`) 
                VALUES ('Alta', :asset_id, :resp_id, :office_id, :loc_id, :entry_date, 'Registro inicial de alta del bien patrimonial', :user_id)
            ")->execute([
                'asset_id'   => $assetId,
                'resp_id'    => $responsibleId,
                'office_id'  => $officeId,
                'loc_id'     => $locationId,
                'entry_date' => $entryDate,
                'user_id'    => Session::get('user_id')
            ]);

            $this->response->success("Bien patrimonial registrado exitosamente.");
        } else {
            $this->response->error("Ocurrió un error al registrar el bien patrimonial.");
        }
    }

    /**
     * API AJAX: Actualizar bien patrimonial existente
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ASSET_EDIT');

        $id = (int)$params['id'];
        $asset = $this->assetModel->findWithRelations($id);

        if (!$asset) {
            $this->response->error("El bien patrimonial solicitado no existe.", 404);
            return;
        }

        $customCode      = $this->request->input('custom_code', '');
        $subgroupId      = (int)$this->request->input('subgroup_id', 0);
        $type            = $this->request->input('type', '');
        $entryDate       = $this->request->input('entry_date', '');
        $acquisitionDate = $this->request->input('acquisition_date', '');
        $deliveryDate    = $this->request->input('delivery_date', '');
        $usefulLife      = (int)$this->request->input('useful_life', 0);
        $assetStatus     = $this->request->input('asset_status', 'Bueno');
        $serialNumber    = $this->request->input('serial_number', '');
        $brand           = $this->request->input('brand', '');
        $model           = $this->request->input('model', '');
        $characteristics = $this->request->input('characteristics', '');
        $responsibleId   = (int)$this->request->input('responsible_id', 0);
        $officeId        = (int)$this->request->input('office_id', 0);
        $locationId      = (int)$this->request->input('location_id', 0);
        $fundingSourceId = (int)$this->request->input('funding_source_id', 0);
        $observations    = $this->request->input('observations', '');

        if (empty($customCode) || $subgroupId <= 0 || empty($type) || empty($entryDate) || $responsibleId <= 0 || $officeId <= 0 || $locationId <= 0 || $fundingSourceId <= 0) {
            $this->response->error('Por favor, complete todos los campos obligatorios.');
            return;
        }

        // Validar que el código patrimonial no pertenezca a otro bien activo
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `assets` WHERE `custom_code` = :code AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $customCode, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error('El código patrimonial ingresado ya está registrado en otro bien.');
            return;
        }

        // Cargar foto principal si se subió una nueva
        $mainPhotoPath = $asset['main_photo'];
        if (isset($_FILES['main_photo']) && $_FILES['main_photo']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $this->uploadPhoto($_FILES['main_photo']);
            if ($uploaded) {
                $mainPhotoPath = $uploaded;
            }
        }

        // Evaluar cambios para historial de movimientos
        if ($asset['responsible_id'] != $responsibleId) {
            $db->prepare("
                INSERT INTO `movements` (`movement_type`, `asset_id`, `origin_responsible_id`, `target_responsible_id`, `movement_date`, `observations`, `created_by`) 
                VALUES ('Cambio Responsable', :asset_id, :origin_id, :target_id, CURDATE(), 'Reasignación de custodia de bienes patrimoniales', :user_id)
            ")->execute([
                'asset_id'  => $id,
                'origin_id' => $asset['responsible_id'],
                'target_id' => $responsibleId,
                'user_id'   => Session::get('user_id')
            ]);
        }

        if ($asset['office_id'] != $officeId || $asset['location_id'] != $locationId) {
            $db->prepare("
                INSERT INTO `movements` (`movement_type`, `asset_id`, `origin_office_id`, `target_office_id`, `origin_location_id`, `target_location_id`, `movement_date`, `observations`, `created_by`) 
                VALUES ('Cambio Ubicacion', :asset_id, :origin_off, :target_off, :origin_loc, :target_loc, CURDATE(), 'Traslado físico del bien patrimonial', :user_id)
            ")->execute([
                'asset_id'   => $id,
                'origin_off' => $asset['office_id'],
                'target_off' => $officeId,
                'origin_loc' => $asset['location_id'],
                'target_loc' => $locationId,
                'user_id'    => Session::get('user_id')
            ]);
        }

        // Actualizar datos del bien
        $this->assetModel->update($id, [
            'custom_code'       => $customCode,
            'subgroup_id'       => $subgroupId,
            'type'              => $type,
            'entry_date'        => $entryDate,
            'acquisition_date'  => !empty($acquisitionDate) ? $acquisitionDate : null,
            'delivery_date'     => !empty($deliveryDate) ? $deliveryDate : null,
            'useful_life'       => $usefulLife,
            'asset_status'      => $assetStatus,
            'serial_number'     => !empty($serialNumber) ? $serialNumber : null,
            'brand'             => !empty($brand) ? $brand : null,
            'model'             => !empty($model) ? $model : null,
            'characteristics'   => !empty($characteristics) ? $characteristics : null,
            'responsible_id'    => $responsibleId,
            'office_id'         => $officeId,
            'location_id'       => $locationId,
            'funding_source_id' => $fundingSourceId,
            'observations'      => !empty($observations) ? $observations : null,
            'main_photo'        => $mainPhotoPath
        ]);

        // Fotos adicionales (opcional en edición)
        if (isset($_FILES['photos'])) {
            $additionalPhotos = $this->uploadMultiplePhotos($_FILES['photos']);
            if (!empty($additionalPhotos)) {
                $this->assetModel->savePhotos($id, $additionalPhotos);
            }
        }

        $this->response->success("Bien patrimonial actualizado correctamente.");
    }

    /**
     * Generar y guardar la imagen QR a nivel local
     */
    private function generateAndSaveQr(int $assetId, string $customCode): string
    {
        $qrData = BASE_URL . "/bienes/ficha/" . $assetId;
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);

        // Crear directorios si no existen
        $uploadDir = ROOT_DIR . '/public/assets/uploads/qrs';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'qr_' . $assetId . '_' . time() . '.png';
        $savePath = $uploadDir . '/' . $fileName;

        // Descargar la imagen
        $imgContent = @file_get_contents($qrUrl);
        if ($imgContent !== false) {
            file_put_contents($savePath, $imgContent);
            return '/public/assets/uploads/qrs/' . $fileName;
        }

        return '';
    }

    /**
     * Auxiliar de carga de archivos (foto principal)
     */
    private function uploadPhoto(array $file): ?string
    {
        $uploadDir = ROOT_DIR . '/public/assets/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array(strtolower($ext), $allowed)) {
            return null;
        }

        $fileName = 'asset_' . uniqid() . '_' . time() . '.' . $ext;
        $target = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return '/public/assets/uploads/' . $fileName;
        }

        return null;
    }

    /**
     * Auxiliar de carga de múltiples fotos adicionales
     */
    private function uploadMultiplePhotos(array $files): array
    {
        $uploadedPaths = [];
        $uploadDir = ROOT_DIR . '/public/assets/uploads';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), $allowed)) {
                    $fileName = 'asset_extra_' . uniqid() . '_' . $i . '_' . time() . '.' . $ext;
                    $target = $uploadDir . '/' . $fileName;

                    if (move_uploaded_file($files['tmp_name'][$i], $target)) {
                        $uploadedPaths[] = '/public/assets/uploads/' . $fileName;
                    }
                }
            }
        }

        return $uploadedPaths;
    }

    /**
     * Convertir cadenas de UTF-8 a ISO-8859-1 para compatibilidad con FPDF
     */
    private function cleanString(string $str): string
    {
        if (function_exists('iconv')) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $str);
        }
        return utf8_decode($str);
    }
}
