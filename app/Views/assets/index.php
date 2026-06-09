<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Inventario de Bienes Patrimoniales</h5>
            <p class="text-muted small m-0">Consulte, exporte y gestione el catálogo general de activos del patrimonio institucional.</p>
        </div>
        <div>
            <?php if (in_array('ASSET_EXPORT', $userPermissions)): ?>
                <a href="<?= BASE_URL ?>/bienes/exportar" class="btn btn-outline-success rounded-3 me-2">
                    <i class="fa-solid fa-file-excel me-2"></i> Exportar Excel
                </a>
            <?php endif; ?>
            <?php if (in_array('ASSET_CREATE', $userPermissions)): ?>
                <button type="button" class="btn btn-primary rounded-3 btn-create-asset">
                    <i class="fa-solid fa-plus me-2"></i> Registrar Bien
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="assetsTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Código Patr.</th>
                                <th>Bien / Modelo</th>
                                <th>Grupo</th>
                                <th>Responsable</th>
                                <th>Área / Oficina</th>
                                <th>Estado</th>
                                <th class="text-center" style="width: 130px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assets as $asset): ?>
                                <tr id="asset_row_<?= $asset['id'] ?>">
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($asset['custom_code']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($asset['type']) ?></div>
                                        <span class="text-muted small fs-7"><?= htmlspecialchars($asset['brand'] ?? 'S/M') ?> - <?= htmlspecialchars($asset['model'] ?? 'S/M') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($asset['group_name']) ?></td>
                                    <td class="small"><?= htmlspecialchars($asset['responsible_name']) ?></td>
                                    <td class="small"><?= htmlspecialchars($asset['office_name']) ?></td>
                                    <td>
                                        <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8
                                            <?php 
                                                if ($asset['asset_status'] === 'Bueno') echo 'bg-success text-success';
                                                elseif ($asset['asset_status'] === 'Regular') echo 'bg-primary text-primary';
                                                elseif ($asset['asset_status'] === 'Malo') echo 'bg-warning text-warning';
                                                else echo 'bg-danger text-danger';
                                            ?>">
                                            <?= htmlspecialchars($asset['asset_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                             <button type="button" class="btn btn-sm btn-light text-secondary rounded-3 btn-detail-asset" data-id="<?= $asset['id'] ?>" title="Ver Detalle y QR">
                                                 <i class="fa-solid fa-eye"></i>
                                             </button>
                                             <?php if (in_array('ASSET_EDIT', $userPermissions)): ?>
                                                 <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-asset" data-id="<?= $asset['id'] ?>" title="Editar">
                                                     <i class="fa-solid fa-pen"></i>
                                                 </button>
                                             <?php endif; ?>
                                            <?php if (in_array('ASSET_DELETE', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-asset" data-id="<?= $asset['id'] ?>" data-code="<?= htmlspecialchars($asset['custom_code']) ?>" title="Dar de Baja / Eliminar">
                                                    <i class="fa-solid fa-trash-can"></i>
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
   MODAL PARA REGISTRAR / EDITAR BIEN PATRIMONIAL (AJAX)
   ========================================================================== -->
<div class="modal fade" id="assetModal" tabindex="-1" aria-labelledby="assetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="assetForm" class="modal-content border-0 shadow-lg rounded-4" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="assetModalLabel"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Registrar Bien Patrimonial</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="modal-body p-4">
                    <!-- SECCIÓN 1: Identificación y Clasificación -->
                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="fw-bold text-primary mb-3" style="font-size:0.9rem;"><i class="fa-solid fa-tags me-2"></i> 1. Identificación y Clasificación</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="custom_code" class="form-label small fw-semibold text-secondary">Código Patrimonial <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="custom_code" name="custom_code" placeholder="Ej. PAT-00123" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="group_id" class="form-label small fw-semibold text-secondary">Grupo Genérico <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="group_id" required>
                                    <option value="" disabled selected>Seleccione grupo...</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['code']) ?> - <?= htmlspecialchars($group['description']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="subgroup_id" class="form-label small fw-semibold text-secondary">Subgrupo de Bien <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="subgroup_id" name="subgroup_id" required disabled>
                                    <option value="" disabled selected>Seleccione grupo primero...</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="type" class="form-label small fw-semibold text-secondary">Tipo de Activo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="type" name="type" placeholder="Ej. Equipo de Cómputo" required>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: Características y Fabricación -->
                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="fw-bold text-primary mb-3" style="font-size:0.9rem;"><i class="fa-solid fa-circle-info me-2"></i> 2. Características y Fabricación</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="brand" class="form-label small fw-semibold text-secondary">Marca</label>
                                <input type="text" class="form-control rounded-3" id="brand" name="brand" placeholder="Ej. Dell, HP, Lenovo">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="model" class="form-label small fw-semibold text-secondary">Modelo</label>
                                <input type="text" class="form-control rounded-3" id="model" name="model" placeholder="Ej. Latitude 5420">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="serial_number" class="form-label small fw-semibold text-secondary">Número de Serie</label>
                                <input type="text" class="form-control rounded-3" id="serial_number" name="serial_number" placeholder="Ej. S/N 9D8F7G">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="asset_status" class="form-label small fw-semibold text-secondary">Estado de Conservación <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="asset_status" name="asset_status" required>
                                    <option value="Bueno">Bueno</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Malo">Malo</option>
                                    <option value="Chatarra">Chatarra</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="entry_date" class="form-label small fw-semibold text-secondary">Fecha de Ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-3" id="entry_date" name="entry_date" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="acquisition_date" class="form-label small fw-semibold text-secondary">Fecha de Alta</label>
                                <input type="date" class="form-control rounded-3" id="acquisition_date" name="acquisition_date">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="delivery_date" class="form-label small fw-semibold text-secondary">Fecha de Entrega</label>
                                <input type="date" class="form-control rounded-3" id="delivery_date" name="delivery_date">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="useful_life" class="form-label small fw-semibold text-secondary">Vida Útil (Meses)</label>
                                <input type="number" class="form-control rounded-3" id="useful_life" name="useful_life" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: Custodia, Área y Financiamiento -->
                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="fw-bold text-primary mb-3" style="font-size:0.9rem;"><i class="fa-solid fa-location-dot me-2"></i> 3. Custodia, Área y Financiamiento</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="responsible_id" class="form-label small fw-semibold text-secondary">Responsable Custodio <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="responsible_id" name="responsible_id" required>
                                    <option value="" disabled selected>Seleccione responsable...</option>
                                    <?php foreach ($responsibles as $resp): ?>
                                        <option value="<?= $resp['id'] ?>"><?= htmlspecialchars($resp['names']) ?> <?= htmlspecialchars($resp['surnames']) ?> - (<?= htmlspecialchars($resp['position']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="office_id" class="form-label small fw-semibold text-secondary">Unidad Orgánica / Área <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="office_id" name="office_id" required>
                                    <option value="" disabled selected>Seleccione área...</option>
                                    <?php foreach ($offices as $office): ?>
                                        <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="location_id" class="form-label small fw-semibold text-secondary">Localidad / Sede <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="location_id" name="location_id" required>
                                    <option value="" disabled selected>Seleccione sede...</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="funding_source_id" class="form-label small fw-semibold text-secondary">Fuente de Financiamiento <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="funding_source_id" name="funding_source_id" required>
                                    <option value="" disabled selected>Seleccione fuente...</option>
                                    <?php foreach ($fundings as $funding): ?>
                                        <option value="<?= $funding['id'] ?>"><?= htmlspecialchars($funding['code']) ?> - <?= htmlspecialchars($funding['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: Archivos e Imágenes -->
                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="fw-bold text-primary mb-3" style="font-size:0.9rem;"><i class="fa-solid fa-image me-2"></i> 4. Archivos y Galería de Imágenes</h6>
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3">
                                <label for="main_photo" class="form-label small fw-semibold text-secondary">Fotografía Principal (Ficha)</label>
                                <input type="file" class="form-control rounded-3" id="main_photo" name="main_photo" accept="image/*">
                                <span class="text-muted fs-7 d-block">Soporta formatos: JPG, JPEG, PNG, WEBP.</span>
                                
                                <div id="current_photo_container" class="mt-2 d-none">
                                    <span class="text-secondary small d-block mb-1">Imagen Actual:</span>
                                    <img id="current_photo_preview" src="" class="rounded-3 shadow-xs img-thumbnail" style="max-height: 80px;" alt="Bien">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="photos" class="form-label small fw-semibold text-secondary">Fotografías Adicionales (Opcional)</label>
                                <input type="file" class="form-control rounded-3" id="photos" name="photos[]" accept="image/*" multiple>
                                <span class="text-muted fs-7">Puede seleccionar múltiples imágenes de soporte.</span>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 5: Características y Observaciones -->
                    <div class="mb-0">
                        <h6 class="fw-bold text-primary mb-3" style="font-size:0.9rem;"><i class="fa-solid fa-align-left me-2"></i> 5. Características y Observaciones</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="characteristics" class="form-label small fw-semibold text-secondary">Características Particulares</label>
                                <textarea class="form-control rounded-3" id="characteristics" name="characteristics" rows="3" placeholder="Ej. Color negro, carcasa metálica, pantalla de 14 pulgadas..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="observations" class="form-label small fw-semibold text-secondary">Observaciones Generales</label>
                                <textarea class="form-control rounded-3" id="observations" name="observations" rows="3" placeholder="Ej. Entregado con cargador y maletín..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveAsset">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Bien
                    </button>
                </div>
            </form>
        </div>
    </div>

<!-- ==========================================================================
   MODAL PARA VER DETALLE Y QR (AJAX)
   ========================================================================== -->
<div class="modal fade" id="detailAssetModal" tabindex="-1" aria-labelledby="detailAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="detailAssetModalLabel"><i class="fa-solid fa-eye text-primary me-2"></i> Detalle del Bien Patrimonial</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Columna de Imágenes y QR -->
                    <div class="col-md-4 text-center mb-3 mb-md-0 border-end">
                        <div class="mb-4">
                            <span class="text-secondary small fw-semibold d-block mb-2">Fotografía Principal</span>
                            <div id="detail_photo_container" class="border rounded-3 p-2 bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <img id="detail_photo_preview" src="" class="img-fluid rounded-3" style="max-height: 100%; object-fit: contain;" alt="Foto Bien">
                                <div id="detail_photo_placeholder" class="text-muted d-none">
                                    <i class="fa-solid fa-camera fa-2x opacity-50 mb-1 d-block"></i>
                                    <span class="small">Sin fotografía</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <span class="text-secondary small fw-semibold d-block mb-2">Código QR Patrimonial</span>
                            <div class="border rounded-3 p-3 bg-light d-flex align-items-center justify-content-center mx-auto" style="width: 140px; height: 140px;">
                                <img id="detail_qr_preview" src="" class="img-fluid" alt="Código QR">
                            </div>
                            <span class="text-muted fs-8 d-block mt-2 font-monospace" id="detail_custom_code_sub">-</span>
                        </div>
                    </div>

                    <!-- Columna de Datos Técnicos -->
                    <div class="col-md-8 ps-md-4">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle text-dark">
                                <tr>
                                    <td style="width: 150px;" class="text-secondary small fw-semibold">Código Patrimonial:</td>
                                    <td><strong id="detail_custom_code" class="text-primary fs-6">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Tipo de Activo:</td>
                                    <td id="detail_type" class="fw-semibold">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Marca / Modelo:</td>
                                    <td id="detail_brand_model">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Número de Serie:</td>
                                    <td id="detail_serial_number" class="font-monospace small">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Estado de Conservación:</td>
                                    <td><span id="detail_status_badge" class="badge py-1 px-2.5 rounded-pill fs-8">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Fecha de Ingreso:</td>
                                    <td id="detail_entry_date">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Vida Útil:</td>
                                    <td id="detail_useful_life">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Responsable Custodio:</td>
                                    <td id="detail_responsible">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Área / Oficina:</td>
                                    <td id="detail_office">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Sede / Localidad:</td>
                                    <td id="detail_location">-</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary small fw-semibold">Financiamiento:</td>
                                    <td id="detail_funding">-</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <span class="text-secondary small fw-semibold d-block mb-1">Características Particulares:</span>
                            <p id="detail_characteristics" class="small bg-light p-2.5 rounded-3 mb-2 text-muted">-</p>
                        </div>
                        
                        <div>
                            <span class="text-secondary small fw-semibold d-block mb-1">Observaciones:</span>
                            <p id="detail_observations" class="small bg-light p-2.5 rounded-3 text-muted">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4 justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cerrar</button>
                <div class="d-flex gap-2">
                    <a href="#" id="btnPrintLabel" target="_blank" class="btn btn-outline-primary rounded-3">
                        <i class="fa-solid fa-print me-2"></i> Imprimir Etiqueta
                    </a>
                    <a href="#" id="btnPrintFicha" target="_blank" class="btn btn-primary rounded-3">
                        <i class="fa-solid fa-file-pdf me-2"></i> Generar Ficha (PDF)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTable en español
        $('#assetsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        // Configuración de Modales y Formulario
        const assetModal = new bootstrap.Modal(document.getElementById('assetModal'));
        const assetForm = document.getElementById('assetForm');
        let currentActionUrl = '';

        // Combos cascada de Grupo a Subgrupo
        const groupSelect = document.getElementById("group_id");
        const subgroupSelect = document.getElementById("subgroup_id");

        function loadSubgroups(groupId, selectedSubgroupId = null) {
            if (!groupId) {
                subgroupSelect.innerHTML = '<option value="" disabled selected>Seleccione un grupo primero...</option>';
                subgroupSelect.disabled = true;
                return;
            }

            subgroupSelect.disabled = true;
            subgroupSelect.innerHTML = '<option value="" disabled selected>Cargando subgrupos...</option>';

            fetch(`<?= BASE_URL ?>/api/subgroups/by-group/${groupId}`)
                .then(response => response.json())
                .then(data => {
                    subgroupSelect.innerHTML = '<option value="" disabled selected>Seleccione subgrupo...</option>';
                    
                    if (data.length === 0) {
                        subgroupSelect.innerHTML = '<option value="" disabled>Sin subgrupos registrados</option>';
                    } else {
                        data.forEach(sub => {
                            const option = document.createElement("option");
                            option.value = sub.id;
                            option.textContent = `${sub.code} - ${sub.description}`;
                            if (selectedSubgroupId && sub.id == selectedSubgroupId) {
                                option.selected = true;
                            }
                            subgroupSelect.appendChild(option);
                        });
                        subgroupSelect.disabled = false;
                    }
                })
                .catch(error => {
                    console.error("Error al cargar subgrupos:", error);
                    subgroupSelect.innerHTML = '<option value="" disabled>Error al cargar subgrupos</option>';
                });
        }

        groupSelect.addEventListener("change", function() {
            loadSubgroups(this.value);
        });

        // Evento Registrar Bien (Crear)
        $(document).on('click', '.btn-create-asset', function() {
            document.getElementById('assetModalLabel').innerHTML = '<i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Registrar Bien Patrimonial';
            assetForm.reset();
            
            // Resetear selects a opciones default
            groupSelect.value = "";
            subgroupSelect.innerHTML = '<option value="" disabled selected>Seleccione un grupo primero...</option>';
            subgroupSelect.disabled = true;
            document.getElementById('responsible_id').value = "";
            document.getElementById('office_id').value = "";
            document.getElementById('location_id').value = "";
            document.getElementById('funding_source_id').value = "";
            document.getElementById('asset_status').value = "Bueno";
            
            // Resetear foto preview
            document.getElementById('current_photo_container').classList.add('d-none');
            document.getElementById('current_photo_preview').src = "";
            
            currentActionUrl = `<?= BASE_URL ?>/api/bienes/guardar`;
            assetModal.show();
        });

        // Evento Editar Bien
        $(document).on('click', '.btn-edit-asset', function() {
            const assetId = $(this).data('id');
            document.getElementById('assetModalLabel').innerHTML = '<i class="fa-solid fa-pen-to-square text-primary me-2"></i> Editar Bien Patrimonial';
            assetForm.reset();
            
            showLoader('Cargando información del bien...');
            
            // Cargar datos por AJAX
            $.ajax({
                url: `<?= BASE_URL ?>/api/bienes/detalle/${assetId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const asset = response.data;
                        
                        document.getElementById('custom_code').value = asset.custom_code;
                        document.getElementById('type').value = asset.type;
                        document.getElementById('brand').value = asset.brand || '';
                        document.getElementById('model').value = asset.model || '';
                        document.getElementById('serial_number').value = asset.serial_number || '';
                        document.getElementById('asset_status').value = asset.asset_status;
                        document.getElementById('entry_date').value = asset.entry_date;
                        document.getElementById('acquisition_date').value = asset.acquisition_date || '';
                        document.getElementById('delivery_date').value = asset.delivery_date || '';
                        document.getElementById('useful_life').value = asset.useful_life || 0;
                        document.getElementById('responsible_id').value = asset.responsible_id;
                        document.getElementById('office_id').value = asset.office_id;
                        document.getElementById('location_id').value = asset.location_id;
                        document.getElementById('funding_source_id').value = asset.funding_source_id;
                        document.getElementById('characteristics').value = asset.characteristics || '';
                        document.getElementById('observations').value = asset.observations || '';
                        
                        // Cargar grupo y subgrupos
                        groupSelect.value = asset.group_id;
                        loadSubgroups(asset.group_id, asset.subgroup_id);
                        
                        // Mostrar vista previa foto principal
                        if (asset.main_photo) {
                            document.getElementById('current_photo_preview').src = `<?= BASE_URL ?>${asset.main_photo}`;
                            document.getElementById('current_photo_container').classList.remove('d-none');
                        } else {
                            document.getElementById('current_photo_container').classList.add('d-none');
                        }
                        
                        currentActionUrl = `<?= BASE_URL ?>/api/bienes/actualizar/${assetId}`;
                        assetModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información del bien.', 'error');
                }
            });
        });

        // Evento Enviar Formulario (Submit con FormData para soporte de subida de archivos)
        assetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            showLoader('Guardando cambios en el bien patrimonial...');
            
            $.ajax({
                url: currentActionUrl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        assetModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Operación Exitosa!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // Recargar la página para ver reflejados los cambios
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    let errorMsg = 'No se pudo guardar la información del bien patrimonial.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Manejar eliminación AJAX con SweetAlert2
        $(document).on('click', '.btn-delete-asset', function() {
            const assetId = $(this).data('id');
            const assetCode = $(this).data('code');

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `El bien patrimonial con código "${assetCode}" será dado de baja lógica del inventario activo.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, dar de baja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Registrando baja del bien patrimonial...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/bienes/eliminar/${assetId}`,
                        type: 'POST',
                        data: {
                            csrf_token: '<?= $csrf_token ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            hideLoader();
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Dado de Baja',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                // Eliminar la fila de la tabla de forma visual
                                $('#assetsTable').DataTable().row(`#asset_row_${assetId}`).remove().draw();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            hideLoader();
                            Swal.fire('Error', 'No se pudo completar la solicitud de baja. Verifique sus permisos.', 'error');
                        }
                    });
                }
            });
        });

        // Configuración de Modal de Detalle
        const detailAssetModal = new bootstrap.Modal(document.getElementById('detailAssetModal'));

        $(document).on('click', '.btn-detail-asset', function() {
            const assetId = $(this).data('id');
            
            showLoader('Cargando detalle del bien patrimonial...');
            
            $.ajax({
                url: `<?= BASE_URL ?>/api/bienes/detalle/${assetId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const asset = response.data;
                        
                        // Cargar datos en el modal de detalle
                        document.getElementById('detail_custom_code').textContent = asset.custom_code;
                        document.getElementById('detail_custom_code_sub').textContent = asset.custom_code;
                        document.getElementById('detail_type').textContent = asset.type;
                        document.getElementById('detail_brand_model').textContent = `${asset.brand || 'S/M'} / ${asset.model || 'S/M'}`;
                        document.getElementById('detail_serial_number').textContent = asset.serial_number || 'S/N';
                        document.getElementById('detail_entry_date').textContent = asset.entry_date ? asset.entry_date.split('-').reverse().join('/') : '-';
                        document.getElementById('detail_useful_life').textContent = `${asset.useful_life || 0} meses`;
                        document.getElementById('detail_responsible').textContent = asset.responsible_name || '-';
                        document.getElementById('detail_office').textContent = asset.office_name || '-';
                        document.getElementById('detail_location').textContent = asset.location_name || '-';
                        document.getElementById('detail_funding').textContent = asset.funding_name || '-';
                        document.getElementById('detail_characteristics').textContent = asset.characteristics || 'Ninguna característica particular.';
                        document.getElementById('detail_observations').textContent = asset.observations || 'Ninguna observación.';
                        
                        // Estado Físico Badge
                        const statusBadge = document.getElementById('detail_status_badge');
                        statusBadge.textContent = asset.asset_status;
                        statusBadge.className = 'badge py-1 px-2.5 rounded-pill fs-8 ';
                        if (asset.asset_status === 'Bueno') {
                            statusBadge.classList.add('bg-success', 'bg-opacity-10', 'text-success');
                        } else if (asset.asset_status === 'Regular') {
                            statusBadge.classList.add('bg-primary', 'bg-opacity-10', 'text-primary');
                        } else if (asset.asset_status === 'Malo') {
                            statusBadge.classList.add('bg-warning', 'bg-opacity-10', 'text-warning');
                        } else {
                            statusBadge.classList.add('bg-danger', 'bg-opacity-10', 'text-danger');
                        }
                        
                        // Foto Principal Preview
                        const photoPreview = document.getElementById('detail_photo_preview');
                        const photoPlaceholder = document.getElementById('detail_photo_placeholder');
                        if (asset.main_photo) {
                            photoPreview.src = `<?= BASE_URL ?>${asset.main_photo}`;
                            photoPreview.classList.remove('d-none');
                            photoPlaceholder.classList.add('d-none');
                        } else {
                            photoPreview.src = "";
                            photoPreview.classList.add('d-none');
                            photoPlaceholder.classList.remove('d-none');
                        }
                        
                        // Código QR Preview
                        const qrPreview = document.getElementById('detail_qr_preview');
                        if (asset.qr_code) {
                            qrPreview.src = `<?= BASE_URL ?>${asset.qr_code}`;
                        } else {
                            qrPreview.src = "";
                        }
                        
                        // Configurar enlaces de los botones de impresión en el pie
                        document.getElementById('btnPrintFicha').href = `<?= BASE_URL ?>/bienes/ficha/${asset.id}`;
                        document.getElementById('btnPrintLabel').href = `<?= BASE_URL ?>/bienes/etiqueta/${asset.id}?print=true`;
                        
                        detailAssetModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar el detalle del bien patrimonial.', 'error');
                }
            });
        });
    });
</script>
