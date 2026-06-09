<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Oficinas / Áreas</h5>
            <p class="text-muted small m-0">Administre las oficinas y áreas orgánicas que conforman la estructura organizacional de EPS RIOJA.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-building me-2 text-secondary"></i> Listado de Oficinas / Áreas</h6>
                <?php if (in_array('ROLE_CREATE', $userPermissions)): ?>
                    <button type="button" class="btn btn-primary rounded-3 btn-create-office">
                        <i class="fa-solid fa-plus me-1"></i> Registrar Oficina
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="officesTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Código</th>
                                <th>Nombre de la Oficina / Área</th>
                                <th style="width: 150px;">Estado</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offices as $office): ?>
                                <tr id="office_row_<?= $office['id'] ?>">
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($office['code']) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($office['name']) ?></td>
                                    <td>
                                        <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 <?= $office['status'] == 1 ? 'bg-success text-success' : 'bg-danger text-danger' ?>">
                                            <?= $office['status'] == 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php if (in_array('ROLE_EDIT', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-office" data-id="<?= $office['id'] ?>" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array('ROLE_DELETE', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-office" data-id="<?= $office['id'] ?>" data-code="<?= htmlspecialchars($office['code']) ?>" data-name="<?= htmlspecialchars($office['name']) ?>" title="Eliminar">
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
   MODAL PARA OFICINAS (REGISTRAR / EDITAR)
   ========================================================================== -->
<div class="modal fade" id="officeModal" tabindex="-1" aria-labelledby="officeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="officeModalLabel"><i class="fa-solid fa-building text-primary me-2"></i> Registrar Oficina</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="officeForm" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="office_code" class="form-label small fw-semibold text-secondary">Código de Oficina <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="office_code" name="code" placeholder="Ej. OFI-001" required>
                    </div>
                    <div class="mb-3">
                        <label for="office_name" class="form-label small fw-semibold text-secondary">Nombre de Oficina / Área <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="office_name" name="name" placeholder="Ej. Oficina de Tecnología de la Información" required>
                    </div>
                    <div>
                        <label for="office_status" class="form-label small fw-semibold text-secondary">Estado</label>
                        <select class="form-select rounded-3" id="office_status" name="status">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveOffice">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Oficina
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTable en español
        $('#officesTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        // Instancia de Modal
        const officeModal = new bootstrap.Modal(document.getElementById('officeModal'));
        const officeForm = document.getElementById('officeForm');
        let actionUrl = '';

        // Botón: Registrar Oficina
        $(document).on('click', '.btn-create-office', function() {
            document.getElementById('officeModalLabel').innerHTML = '<i class="fa-solid fa-building text-primary me-2"></i> Registrar Oficina / Área';
            officeForm.reset();
            document.getElementById('office_status').value = "1";
            actionUrl = `<?= BASE_URL ?>/api/offices/guardar`;
            officeModal.show();
        });

        // Botón: Editar Oficina
        $(document).on('click', '.btn-edit-office', function() {
            const id = $(this).data('id');
            document.getElementById('officeModalLabel').innerHTML = '<i class="fa-solid fa-pen text-primary me-2"></i> Editar Oficina / Área';
            officeForm.reset();

            showLoader('Obteniendo información de la oficina...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/offices/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const office = response.data;
                        document.getElementById('office_code').value = office.code;
                        document.getElementById('office_name').value = office.name;
                        document.getElementById('office_status').value = office.status;

                        actionUrl = `<?= BASE_URL ?>/api/offices/actualizar/${id}`;
                        officeModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información de la oficina.', 'error');
                }
            });
        });

        // Formulario: Guardar / Actualizar
        officeForm.addEventListener('submit', function(e) {
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
                        officeModal.hide();
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
                    let errorMsg = 'No se pudo guardar la información de la oficina.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Botón: Eliminar Oficina
        $(document).on('click', '.btn-delete-office', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            const name = $(this).data('name');

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `La oficina con código "${code}" (${name}) será dada de baja de la base de datos activa.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Eliminando oficina...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/offices/eliminar/${id}`,
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
                                    title: 'Eliminada',
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
                            let errorMsg = 'No se pudo procesar la eliminación de la oficina.';
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
