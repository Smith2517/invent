<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Locales / Sedes</h5>
            <p class="text-muted small m-0">Administre los locales físicos, sedes y establecimientos operativos de EPS RIOJA.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-map-location-dot me-2 text-secondary"></i> Listado de Locales / Sedes</h6>
                <?php if (in_array('ROLE_CREATE', $userPermissions)): ?>
                    <button type="button" class="btn btn-primary rounded-3 btn-create-location">
                        <i class="fa-solid fa-plus me-1"></i> Registrar Local
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="locationsTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Código</th>
                                <th>Nombre del Local / Sede</th>
                                <th>Dirección Física</th>
                                <th style="width: 150px;">Estado</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $loc): ?>
                                <tr id="location_row_<?= $loc['id'] ?>">
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($loc['code']) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($loc['name']) ?></td>
                                    <td><?= htmlspecialchars($loc['address'] ?? 'No especificada') ?></td>
                                    <td>
                                        <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 <?= $loc['status'] == 1 ? 'bg-success text-success' : 'bg-danger text-danger' ?>">
                                            <?= $loc['status'] == 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php if (in_array('ROLE_EDIT', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-location" data-id="<?= $loc['id'] ?>" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array('ROLE_DELETE', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-location" data-id="<?= $loc['id'] ?>" data-code="<?= htmlspecialchars($loc['code']) ?>" data-name="<?= htmlspecialchars($loc['name']) ?>" title="Eliminar">
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
   MODAL PARA LOCALES / SEDES (REGISTRAR / EDITAR)
   ========================================================================== -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="locationModalLabel"><i class="fa-solid fa-map-location-dot text-primary me-2"></i> Registrar Local</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="locationForm" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="location_code" class="form-label small fw-semibold text-secondary">Código del Local <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="location_code" name="code" placeholder="Ej. LOC-ADM" required>
                    </div>
                    <div class="mb-3">
                        <label for="location_name" class="form-label small fw-semibold text-secondary">Nombre del Local / Sede <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="location_name" name="name" placeholder="Ej. Sede Central Administrativa" required>
                    </div>
                    <div class="mb-3">
                        <label for="location_address" class="form-label small fw-semibold text-secondary">Dirección Física</label>
                        <input type="text" class="form-control rounded-3" id="location_address" name="address" placeholder="Ej. Jr. San Martín N° 450 - Rioja">
                    </div>
                    <div>
                        <label for="location_status" class="form-label small fw-semibold text-secondary">Estado</label>
                        <select class="form-select rounded-3" id="location_status" name="status">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveLocation">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Local
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTable en español
        $('#locationsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        // Instancia de Modal
        const locationModal = new bootstrap.Modal(document.getElementById('locationModal'));
        const locationForm = document.getElementById('locationForm');
        let actionUrl = '';

        // Botón: Registrar Local
        $(document).on('click', '.btn-create-location', function() {
            document.getElementById('locationModalLabel').innerHTML = '<i class="fa-solid fa-map-location-dot text-primary me-2"></i> Registrar Local / Sede';
            locationForm.reset();
            document.getElementById('location_status').value = "1";
            actionUrl = `<?= BASE_URL ?>/api/locations/guardar`;
            locationModal.show();
        });

        // Botón: Editar Local
        $(document).on('click', '.btn-edit-location', function() {
            const id = $(this).data('id');
            document.getElementById('locationModalLabel').innerHTML = '<i class="fa-solid fa-pen text-primary me-2"></i> Editar Local / Sede';
            locationForm.reset();

            showLoader('Obteniendo información del local...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/locations/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const location = response.data;
                        document.getElementById('location_code').value = location.code;
                        document.getElementById('location_name').value = location.name;
                        document.getElementById('location_address').value = location.address || '';
                        document.getElementById('location_status').value = location.status;

                        actionUrl = `<?= BASE_URL ?>/api/locations/actualizar/${id}`;
                        locationModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información del local / sede.', 'error');
                }
            });
        });

        // Formulario: Guardar / Actualizar
        locationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            showLoader('Guardando cambios...');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        locationModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Operación Exitosa!',
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
                error: function(xhr) {
                    hideLoader();
                    let errorMsg = 'No se pudo guardar la información del local.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Botón: Eliminar Local
        $(document).on('click', '.btn-delete-location', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            const name = $(this).data('name');

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `El local / sede con código "${code}" (${name}) será dado de baja.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Eliminando local...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/locations/eliminar/${id}`,
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
                                    title: 'Eliminado',
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
                        error: function(xhr) {
                            hideLoader();
                            let errorMsg = 'No se pudo completar la solicitud de eliminación del local.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
