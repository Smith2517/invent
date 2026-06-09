<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Fuentes de Financiamiento</h5>
            <p class="text-muted small m-0">Administre los orígenes de financiamiento y presupuestos asignados para la adquisición de bienes en EPS RIOJA.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-coins me-2 text-secondary"></i> Listado de Fuentes de Financiamiento</h6>
                <?php if (in_array('ROLE_CREATE', $userPermissions)): ?>
                    <button type="button" class="btn btn-primary rounded-3 btn-create-source">
                        <i class="fa-solid fa-plus me-1"></i> Registrar Fuente
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="sourcesTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Código</th>
                                <th>Nombre de la Fuente de Financiamiento</th>
                                <th style="width: 150px;">Estado</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sources as $source): ?>
                                <tr id="source_row_<?= $source['id'] ?>">
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($source['code']) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($source['name']) ?></td>
                                    <td>
                                        <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 <?= $source['status'] == 1 ? 'bg-success text-success' : 'bg-danger text-danger' ?>">
                                            <?= $source['status'] == 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php if (in_array('ROLE_EDIT', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-source" data-id="<?= $source['id'] ?>" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array('ROLE_DELETE', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-source" data-id="<?= $source['id'] ?>" data-code="<?= htmlspecialchars($source['code']) ?>" data-name="<?= htmlspecialchars($source['name']) ?>" title="Eliminar">
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
   MODAL PARA FUENTES DE FINANCIAMIENTO (REGISTRAR / EDITAR)
   ========================================================================== -->
<div class="modal fade" id="sourceModal" tabindex="-1" aria-labelledby="sourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="sourceModalLabel"><i class="fa-solid fa-coins text-primary me-2"></i> Registrar Fuente</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sourceForm" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="source_code" class="form-label small fw-semibold text-secondary">Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="source_code" name="code" placeholder="Ej. RO (Recursos Ordinarios)" required>
                    </div>
                    <div class="mb-3">
                        <label for="source_name" class="form-label small fw-semibold text-secondary">Nombre de la Fuente <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="source_name" name="name" placeholder="Ej. RECURSOS ORDINARIOS" required>
                    </div>
                    <div>
                        <label for="source_status" class="form-label small fw-semibold text-secondary">Estado</label>
                        <select class="form-select rounded-3" id="source_status" name="status">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveSource">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Fuente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTable en español
        $('#sourcesTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        // Instancia de Modal
        const sourceModal = new bootstrap.Modal(document.getElementById('sourceModal'));
        const sourceForm = document.getElementById('sourceForm');
        let actionUrl = '';

        // Botón: Registrar Fuente
        $(document).on('click', '.btn-create-source', function() {
            document.getElementById('sourceModalLabel').innerHTML = '<i class="fa-solid fa-coins text-primary me-2"></i> Registrar Fuente de Financiamiento';
            sourceForm.reset();
            document.getElementById('source_status').value = "1";
            actionUrl = `<?= BASE_URL ?>/api/funding-sources/guardar`;
            sourceModal.show();
        });

        // Botón: Editar Fuente
        $(document).on('click', '.btn-edit-source', function() {
            const id = $(this).data('id');
            document.getElementById('sourceModalLabel').innerHTML = '<i class="fa-solid fa-pen text-primary me-2"></i> Editar Fuente de Financiamiento';
            sourceForm.reset();

            showLoader('Obteniendo información de la fuente...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/funding-sources/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const source = response.data;
                        document.getElementById('source_code').value = source.code;
                        document.getElementById('source_name').value = source.name;
                        document.getElementById('source_status').value = source.status;

                        actionUrl = `<?= BASE_URL ?>/api/funding-sources/actualizar/${id}`;
                        sourceModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información de la fuente de financiamiento.', 'error');
                }
            });
        });

        // Formulario: Guardar / Actualizar
        sourceForm.addEventListener('submit', function(e) {
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
                        sourceModal.hide();
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
                    let errorMsg = 'No se pudo guardar la información de la fuente.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Botón: Eliminar Fuente
        $(document).on('click', '.btn-delete-source', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');
            const name = $(this).data('name');

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `La fuente de financiamiento con código "${code}" (${name}) será dada de baja.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Eliminando fuente...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/funding-sources/eliminar/${id}`,
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
                            let errorMsg = 'No se pudo completar la solicitud de eliminación.';
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
