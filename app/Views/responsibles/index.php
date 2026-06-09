<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Responsables de Custodio</h5>
            <p class="text-muted small m-0">Administre al personal encargado de la custodia física y resguardo de los bienes patrimoniales de EPS RIOJA.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-id-card me-2 text-secondary"></i> Listado de Responsables</h6>
                <?php if (in_array('ROLE_CREATE', $userPermissions)): ?>
                    <button type="button" class="btn btn-primary rounded-3 btn-create-responsible">
                        <i class="fa-solid fa-user-plus me-1"></i> Registrar Responsable
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="responsiblesTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">DNI</th>
                                <th>Apellidos y Nombres</th>
                                <th>Cargo</th>
                                <th>Oficina / Área</th>
                                <th>Contacto</th>
                                <th style="width: 100px;">Estado</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responsibles as $resp): ?>
                                <tr id="responsible_row_<?= $resp['id'] ?>">
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($resp['dni']) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($resp['surnames'] . ', ' . $resp['names']) ?></td>
                                    <td><?= htmlspecialchars($resp['position'] ?? 'No especificado') ?></td>
                                    <td><?= htmlspecialchars($resp['office_name']) ?></td>
                                    <td>
                                        <div class="small">
                                            <?php if (!empty($resp['email'])): ?>
                                                <div><i class="fa-regular fa-envelope me-1 text-muted"></i> <?= htmlspecialchars($resp['email']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($resp['phone'])): ?>
                                                <div><i class="fa-solid fa-phone me-1 text-muted"></i> <?= htmlspecialchars($resp['phone']) ?></div>
                                            <?php endif; ?>
                                            <?php if (empty($resp['email']) && empty($resp['phone'])): ?>
                                                <span class="text-muted italic">Sin contacto</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 <?= $resp['status'] == 1 ? 'bg-success text-success' : 'bg-danger text-danger' ?>">
                                            <?= $resp['status'] == 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php if (in_array('ROLE_EDIT', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-responsible" data-id="<?= $resp['id'] ?>" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array('ROLE_DELETE', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-responsible" data-id="<?= $resp['id'] ?>" data-name="<?= htmlspecialchars($resp['names'] . ' ' . $resp['surnames']) ?>" title="Eliminar">
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
   MODAL PARA RESPONSABLES (REGISTRAR / EDITAR)
   ========================================================================== -->
<div class="modal fade" id="responsibleModal" tabindex="-1" aria-labelledby="responsibleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="responsibleForm" autocomplete="off" class="modal-content border-0 shadow-lg rounded-4">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="responsibleModalLabel"><i class="fa-solid fa-id-card text-primary me-2"></i> Registrar Responsable</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="resp_dni" class="form-label small fw-semibold text-secondary">DNI / Documento <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="resp_dni" name="dni" maxlength="20" placeholder="Ingrese DNI" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="resp_office" class="form-label small fw-semibold text-secondary">Oficina / Área <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="resp_office" name="office_id" required>
                            <option value="" disabled selected>Seleccione oficina...</option>
                            <?php foreach ($offices as $office): ?>
                                <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="resp_names" class="form-label small fw-semibold text-secondary">Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="resp_names" name="names" maxlength="100" placeholder="Ingrese nombres" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="resp_surnames" class="form-label small fw-semibold text-secondary">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="resp_surnames" name="surnames" maxlength="100" placeholder="Ingrese apellidos" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="resp_position" class="form-label small fw-semibold text-secondary">Cargo / Puesto</label>
                    <input type="text" class="form-control rounded-3" id="resp_position" name="position" maxlength="100" placeholder="Ej. Especialista en Informática">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="resp_email" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
                        <input type="email" class="form-control rounded-3" id="resp_email" name="email" maxlength="150" placeholder="ejemplo@correo.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="resp_phone" class="form-label small fw-semibold text-secondary">Teléfono / Celular</label>
                        <input type="text" class="form-control rounded-3" id="resp_phone" name="phone" maxlength="30" placeholder="Ej. 987654321">
                    </div>
                </div>

                <div>
                    <label for="resp_status" class="form-label small fw-semibold text-secondary">Estado</label>
                    <select class="form-select rounded-3" id="resp_status" name="status">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveResponsible">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Responsable
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTable en español
        $('#responsiblesTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        // Instancia de Modal
        const responsibleModal = new bootstrap.Modal(document.getElementById('responsibleModal'));
        const responsibleForm = document.getElementById('responsibleForm');
        let actionUrl = '';

        // Botón: Registrar Responsable
        $(document).on('click', '.btn-create-responsible', function() {
            document.getElementById('responsibleModalLabel').innerHTML = '<i class="fa-solid fa-user-plus text-primary me-2"></i> Registrar Responsable de Custodio';
            responsibleForm.reset();
            document.getElementById('resp_office').value = "";
            document.getElementById('resp_status').value = "1";
            actionUrl = `<?= BASE_URL ?>/api/responsibles/guardar`;
            responsibleModal.show();
        });

        // Botón: Editar Responsable
        $(document).on('click', '.btn-edit-responsible', function() {
            const id = $(this).data('id');
            document.getElementById('responsibleModalLabel').innerHTML = '<i class="fa-solid fa-pen text-primary me-2"></i> Editar Responsable de Custodio';
            responsibleForm.reset();

            showLoader('Obteniendo información del responsable...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/responsibles/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const resp = response.data;
                        document.getElementById('resp_dni').value = resp.dni;
                        document.getElementById('resp_names').value = resp.names;
                        document.getElementById('resp_surnames').value = resp.surnames;
                        document.getElementById('resp_position').value = resp.position || '';
                        document.getElementById('resp_office').value = resp.office_id;
                        document.getElementById('resp_email').value = resp.email || '';
                        document.getElementById('resp_phone').value = resp.phone || '';
                        document.getElementById('resp_status').value = resp.status;

                        actionUrl = `<?= BASE_URL ?>/api/responsibles/actualizar/${id}`;
                        responsibleModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información del responsable.', 'error');
                }
            });
        });

        // Formulario: Guardar / Actualizar
        responsibleForm.addEventListener('submit', function(e) {
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
                        responsibleModal.hide();
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
                    let errorMsg = 'No se pudo guardar la información del responsable.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Botón: Eliminar Responsable
        $(document).on('click', '.btn-delete-responsible', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `El responsable "${name}" será dado de baja del catálogo activo.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Dando de baja al responsable...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/responsibles/eliminar/${id}`,
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
                                    title: 'Dado de baja',
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
                            let errorMsg = 'No se pudo procesar la baja del responsable.';
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
