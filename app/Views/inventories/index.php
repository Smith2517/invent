<?php
// Contar estadísticas rápidas
$total = count($inventoryList);
$verified = 0;
$missing = 0;

foreach ($inventoryList as $item) {
    if ($item['verification_id'] !== null) {
        if ($item['found'] == 1) {
            $verified++;
        } else {
            $missing++;
        }
    }
}
$pending = $total - ($verified + $missing);
?>

<!-- Cabecera del Módulo -->
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Inventario Físico de Bienes</h5>
            <p class="text-muted small m-0">Realiza la inspección de activos in situ, registra su estado de conservación y ubicación geográfica mediante GPS.</p>
        </div>
    </div>
</div>

<!-- Fila de Tarjetas de Estadísticas del Inventario -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-3 mb-md-0">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Total de Bienes</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0"><?= $total ?></h4>
                </div>
                <div class="kpi-icon bg-secondary bg-opacity-10 text-secondary" style="width:40px; height:40px; font-size:1.2rem;">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-3 mb-md-0">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Verificados (Encontrados)</span>
                    <h4 class="fw-bold text-success mt-1 mb-0"><?= $verified ?></h4>
                </div>
                <div class="kpi-icon bg-success bg-opacity-10 text-success" style="width:40px; height:40px; font-size:1.2rem;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-3 mb-md-0">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Faltantes (No Encontrados)</span>
                    <h4 class="fw-bold text-danger mt-1 mb-0"><?= $missing ?></h4>
                </div>
                <div class="kpi-icon bg-danger bg-opacity-10 text-danger" style="width:40px; height:40px; font-size:1.2rem;">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">Pendientes</span>
                    <h4 class="fw-bold text-warning mt-1 mb-0"><?= $pending ?></h4>
                </div>
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning" style="width:40px; height:40px; font-size:1.2rem;">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado DataTable -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="inventoryTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Código Patr.</th>
                                <th>Bien / Modelo</th>
                                <th>Responsable Custodio</th>
                                <th>Sede / Ubicación</th>
                                <th>Estado Físico</th>
                                <th>Fecha Inspección</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventoryList as $item): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($item['custom_code']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($item['type']) ?></div>
                                        <span class="text-muted small fs-8"><?= htmlspecialchars($item['brand'] ?? 'S/M') ?> / <?= htmlspecialchars($item['model'] ?? 'S/M') ?></span>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($item['responsible_name']) ?></td>
                                    <td class="small">
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($item['location_name']) ?></div>
                                        <span class="text-muted fs-8"><?= htmlspecialchars($item['office_name']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($item['verification_id'] === null): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary py-1.5 px-2.5 rounded-pill fs-8">
                                                <i class="fa-solid fa-hourglass me-1"></i> Pendiente
                                            </span>
                                        <?php elseif ($item['found'] == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success py-1.5 px-2.5 rounded-pill fs-8 mb-1" title="Verificado por: <?= htmlspecialchars($item['verified_by_name']) ?>">
                                                <i class="fa-solid fa-check-double me-1"></i> Encontrado (<?= htmlspecialchars($item['verified_status']) ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger py-1.5 px-2.5 rounded-pill fs-8 mb-1" title="Verificado por: <?= htmlspecialchars($item['verified_by_name']) ?>">
                                                <i class="fa-solid fa-xmark me-1"></i> No Encontrado
                                            </span>
                                        <?php endif; ?>
                                        
                                        <!-- Coordenadas GPS Badge -->
                                        <?php if ($item['gps_lat'] && $item['gps_lng']): ?>
                                            <a href="https://www.google.com/maps?q=<?= $item['gps_lat'] ?>,<?= $item['gps_lng'] ?>" target="_blank" class="badge bg-info bg-opacity-10 text-info py-1 px-2 rounded-3 text-decoration-none fs-8 d-block w-fit-content" title="Ver ubicación en Google Maps">
                                                <i class="fa-solid fa-location-crosshairs me-1"></i> GPS
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= $item['verified_at'] ? date('d/m/Y H:i', strtotime($item['verified_at'])) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Acción Verificar / Editar -->
                                            <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-verify" data-id="<?= $item['id'] ?>" data-code="<?= htmlspecialchars($item['custom_code']) ?>" data-type="<?= htmlspecialchars($item['type']) ?>" title="Verificar / Editar Inspección">
                                                <i class="fa-solid fa-clipboard-question"></i>
                                            </button>
                                            
                                            <!-- Resetear Verificación (Solo visible si ya está verificado) -->
                                            <?php if ($item['verification_id'] !== null): ?>
                                                <button type="button" class="btn btn-sm btn-light text-warning rounded-3 btn-reset-verify" data-id="<?= $item['id'] ?>" data-code="<?= htmlspecialchars($item['custom_code']) ?>" title="Restablecer a Pendiente">
                                                    <i class="fa-solid fa-arrows-rotate"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
   MODAL DE VERIFICACIÓN FÍSICA (AJAX)
   ========================================================================== -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="verifyModalLabel"><i class="fa-solid fa-clipboard-check text-primary me-2"></i> Inspección de Bien Patrimonial</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="verifyForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <!-- GPS Data -->
                <input type="hidden" name="gps_lat" id="modal_gps_lat">
                <input type="hidden" name="gps_lng" id="modal_gps_lng">
                
                <div class="modal-body p-4">
                    <!-- Información básica del Bien -->
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="row small">
                            <div class="col-6 mb-1"><span class="text-secondary">Código Patr:</span> <strong class="text-dark" id="modal_asset_code">-</strong></div>
                            <div class="col-6 mb-1"><span class="text-secondary">Tipo de Activo:</span> <strong class="text-dark" id="modal_asset_type">-</strong></div>
                        </div>
                    </div>

                    <!-- Resultado de Verificación -->
                    <div class="mb-3">
                        <label for="modal_found" class="form-label small fw-semibold text-secondary">Resultado de Inspección <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="modal_found" name="found" required>
                            <option value="1">Bien Encontrado / Ubicado</option>
                            <option value="0">Bien No Encontrado / Faltante</option>
                        </select>
                    </div>

                    <!-- Estado Físico Actual -->
                    <div class="mb-3" id="modal_status_group">
                        <label for="modal_status" class="form-label small fw-semibold text-secondary">Estado de Conservación Física <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="modal_status" name="asset_status" required>
                            <option value="Bueno">Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="Malo">Malo</option>
                            <option value="Chatarra">Chatarra</option>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-0">
                        <label for="modal_observations" class="form-label small fw-semibold text-secondary">Observaciones o Comentarios</label>
                        <textarea class="form-control rounded-3" id="modal_observations" name="observations" rows="3" placeholder="Ej. Presenta desgaste en carcasa, operativo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTable en español
        $('#inventoryTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        const verifyModal = new bootstrap.Modal(document.getElementById('verifyModal'));
        let currentAssetId = null;

        // Capturar Geolocalización GPS al cargar la página
        let gpsCoordinates = { lat: null, lng: null };
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                gpsCoordinates.lat = position.coords.latitude;
                gpsCoordinates.lng = position.coords.longitude;
                console.log("Coordenadas obtenidas:", gpsCoordinates);
            });
        }

        // Ocultar o mostrar el selector de estado físico según resultado
        const foundSelect = document.getElementById("modal_found");
        const statusGroup = document.getElementById("modal_status_group");
        const statusSelect = document.getElementById("modal_status");

        foundSelect.addEventListener("change", function() {
            if (foundSelect.value == "0") {
                statusGroup.style.display = "none";
                statusSelect.required = false;
            } else {
                statusGroup.style.display = "block";
                statusSelect.required = true;
            }
        });

        // Abrir Modal de Verificación y cargar datos vía AJAX
        $(document).on('click', '.btn-verify', function() {
            currentAssetId = $(this).data('id');
            const assetCode = $(this).data('code');
            const assetType = $(this).data('type');

            // Cargar datos estáticos en el modal
            document.getElementById('modal_asset_code').textContent = assetCode;
            document.getElementById('modal_asset_type').textContent = assetType;

            // Inyectar coordenadas GPS capturadas
            document.getElementById('modal_gps_lat').value = gpsCoordinates.lat || '';
            document.getElementById('modal_gps_lng').value = gpsCoordinates.lng || '';

            // Resetear inputs del form a valores por defecto
            foundSelect.value = "1";
            statusSelect.value = "Bueno";
            document.getElementById('modal_observations').value = "";
            statusGroup.style.display = "block";

            // Consultar si ya cuenta con verificación para precargar
            $.ajax({
                url: `<?= BASE_URL ?>/api/inventories/detail/${currentAssetId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.id !== null) {
                        const data = response.data;
                        foundSelect.value = data.found;
                        if (data.found == "0") {
                            statusGroup.style.display = "none";
                            statusSelect.required = false;
                        } else {
                            statusSelect.value = data.asset_status || 'Bueno';
                        }
                        document.getElementById('modal_observations').value = data.observations || '';
                    }
                    verifyModal.show();
                },
                error: function() {
                    // Si falla la carga AJAX por alguna razón, abrir el modal vacío
                    verifyModal.show();
                }
            });
        });

        // Guardar Verificación vía AJAX
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: `<?= BASE_URL ?>/api/inventories/verify/${currentAssetId}`,
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        verifyModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Verificado!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // Recargar la página para actualizar las tablas y contadores
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo guardar la verificación física del bien.', 'error');
                }
            });
        });

        // Restablecer Verificación (Reset) vía AJAX con SweetAlert2
        $(document).on('click', '.btn-reset-verify', function() {
            const assetId = $(this).data('id');
            const assetCode = $(this).data('code');

            Swal.fire({
                title: '¿Restablecer verificación?',
                text: `El bien "${assetCode}" volverá al estado 'Pendiente' en el inventario.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, restablecer',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/inventories/reset/${assetId}`,
                        type: 'POST',
                        data: {
                            csrf_token: '<?= $csrf_token ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restablecido',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo restablecer la verificación del bien.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
