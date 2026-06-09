<style>
    /* Estilos específicos para impresión */
    @media print {
        body {
            background-color: #fff !important;
            color: #000 !important;
            font-size: 10pt !important;
        }
        .sidebar, .main-header, .no-print, .btn {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            min-height: auto !important;
        }
        .card {
            box-shadow: none !important;
            border: 0 !important;
            margin-bottom: 0 !important;
        }
        .printable-card {
            border: 1px solid #000 !important;
            border-radius: 0 !important;
            padding: 15px !important;
        }
    }
</style>

<div class="row no-print mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <a href="<?= BASE_URL ?>/bienes" class="btn btn-outline-secondary rounded-3">
            <i class="fa-solid fa-arrow-left me-2"></i> Volver al Listado
        </a>
        <button onclick="window.print()" class="btn btn-primary rounded-3 px-4">
            <i class="fa-solid fa-print me-2"></i> Imprimir Ficha / Acta
        </button>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-8">
        <div class="card border-0 shadow-sm printable-card p-4 bg-white rounded-4">
            <!-- Cabecera del Acta -->
            <div class="row align-items-center border-bottom pb-4 mb-4">
                <div class="col-8">
                    <h5 class="fw-bold text-dark mb-1">FICHA DE CONTROL PATRIMONIAL</h5>
                    <p class="text-muted small mb-0">Acta de Asignación y Responsabilidad de Custodia</p>
                </div>
                <div class="col-4 text-end">
                    <?php if ($asset['qr_code']): ?>
                        <img src="<?= BASE_URL . $asset['qr_code'] ?>" class="img-thumbnail" style="max-height: 90px;" alt="QR Code">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Fila de Fotos e Información Principal -->
            <div class="row mb-4">
                <!-- Foto Principal -->
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <?php if ($asset['main_photo']): ?>
                        <img src="<?= BASE_URL . $asset['main_photo'] ?>" class="img-fluid rounded-3 shadow-xs border" style="max-height: 180px; object-fit: cover;" alt="Bien">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center border rounded-3 text-muted" style="height: 180px;">
                            <i class="fa-solid fa-camera fa-2x opacity-50"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Datos Clave -->
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0 text-dark">
                            <tr>
                                <td style="width: 150px;" class="fw-semibold text-secondary">Código Patrimonial:</td>
                                <td class="fw-bold text-primary fs-5"><?= htmlspecialchars($asset['custom_code']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-secondary">Tipo de Activo:</td>
                                <td class="fw-medium"><?= htmlspecialchars($asset['type']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-secondary">Marca / Modelo:</td>
                                <td><?= htmlspecialchars($asset['brand'] ?? 'S/M') ?> / <?= htmlspecialchars($asset['model'] ?? 'S/M') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-secondary">Número de Serie:</td>
                                <td class="font-monospace small"><?= htmlspecialchars($asset['serial_number'] ?? 'S/N') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-secondary">Estado Físico:</td>
                                <td>
                                    <span class="badge bg-opacity-10 py-1 px-2.5 rounded-pill fs-8
                                        <?php 
                                            if ($asset['asset_status'] === 'Bueno') echo 'bg-success text-success';
                                            elseif ($asset['asset_status'] === 'Regular') echo 'bg-primary text-primary';
                                            elseif ($asset['asset_status'] === 'Malo') echo 'bg-warning text-warning';
                                            else echo 'bg-danger text-danger';
                                        ?>">
                                        <?= htmlspecialchars($asset['asset_status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Fila de Detalles Técnicos -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary"><i class="fa-solid fa-circle-info me-2"></i> Datos Técnicos y Clasificación</h6>
                    <div class="row small">
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Grupo:</span> <?= htmlspecialchars($asset['group_name']) ?>
                        </div>
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Subgrupo:</span> <?= htmlspecialchars($asset['subgroup_name']) ?>
                        </div>
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Fecha de Ingreso:</span> <?= date('d/m/Y', strtotime($asset['entry_date'])) ?>
                        </div>
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Vida Útil Restante:</span> <?= $asset['useful_life'] ?> meses
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila de Ubicación e Identificación de Financiamiento -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary"><i class="fa-solid fa-location-dot me-2"></i> Ubicación e Asignación Presupuestal</h6>
                    <div class="row small">
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Sede / Localidad:</span> <?= htmlspecialchars($asset['location_name']) ?>
                        </div>
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Área / Oficina:</span> <?= htmlspecialchars($asset['office_name']) ?>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="text-secondary fw-semibold">Fuente de Financiamiento:</span> <?= htmlspecialchars($asset['funding_name']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila del Responsable Custodio -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary"><i class="fa-solid fa-user-tie me-2"></i> Personal de Custodia Responsable</h6>
                    <div class="row small">
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Nombre Completo:</span> <?= htmlspecialchars($asset['responsible_names']) ?> <?= htmlspecialchars($asset['responsible_surnames']) ?>
                        </div>
                        <div class="col-6 mb-2">
                            <span class="text-secondary fw-semibold">Cargo Institucional:</span> <?= htmlspecialchars($asset['responsible_position'] ?? 'No especificado') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Características y Observaciones -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary"><i class="fa-solid fa-align-left me-2"></i> Detalles Particulares y Observaciones</h6>
                    <div class="p-3 bg-light rounded-3 small">
                        <div class="mb-2">
                            <strong>Características:</strong><br>
                            <?= nl2br(htmlspecialchars($asset['characteristics'] ?? 'Ninguna característica particular.')) ?>
                        </div>
                        <div>
                            <strong>Observaciones:</strong><br>
                            <?= nl2br(htmlspecialchars($asset['observations'] ?? 'Ninguna observación.')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fotografías Adicionales (Ficha) -->
            <?php if (!empty($photos)): ?>
                <div class="row mb-5 no-print">
                    <div class="col-12">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary"><i class="fa-solid fa-images me-2"></i> Fotografías de Soporte</h6>
                        <div class="d-flex gap-3 flex-wrap">
                            <?php foreach ($photos as $photo): ?>
                                <img src="<?= BASE_URL . $photo['photo_path'] ?>" class="img-thumbnail rounded-3 shadow-xs" style="max-height: 100px; max-width: 100px; object-fit: cover;" alt="Extra Photo">
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Bloque de Firmas para Impresión -->
            <div class="row mt-5 pt-4 text-center">
                <div class="col-6">
                    <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;" class="pt-2 small">
                        <strong>Firma del Custodio Responsable</strong><br>
                        DNI / Nombre del Servidor
                    </div>
                </div>
                <div class="col-6">
                    <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;" class="pt-2 small">
                        <strong>Firma de Control Patrimonial</strong><br>
                        Auditor / Supervisor
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (isset($_GET['print']) && $_GET['print'] === 'true'): ?>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() {
                window.close();
            }, 500);
        }
    </script>
<?php endif; ?>
