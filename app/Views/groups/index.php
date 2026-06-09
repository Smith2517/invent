<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Clasificación de Bienes: Grupos y Subgrupos</h5>
            <p class="text-muted small m-0">Administre los grupos genéricos y los subgrupos específicos para el catálogo general de activos de la institución.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <!-- Pestañas de Navegación -->
        <ul class="nav nav-tabs border-bottom mb-4" id="classificationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold px-4 py-2.5" id="groups-tab" data-bs-toggle="tab" data-bs-target="#groups-pane" type="button" role="tab" aria-controls="groups-pane" aria-selected="true">
                    <i class="fa-solid fa-folder me-2 text-primary"></i> Grupos Genéricos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold px-4 py-2.5" id="subgroups-tab" data-bs-toggle="tab" data-bs-target="#subgroups-pane" type="button" role="tab" aria-controls="subgroups-pane" aria-selected="false">
                    <i class="fa-solid fa-folder-tree me-2 text-primary"></i> Subgrupos Específicos
                </button>
            </li>
        </ul>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content" id="classificationTabsContent">
            <!-- Pestaña Grupos -->
            <div class="tab-pane fade show active" id="groups-pane" role="tabpanel" aria-labelledby="groups-tab" tabindex="0">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
                        <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-table-list me-2 text-secondary"></i> Catálogo de Grupos</h6>
                        <?php if (in_array('ROLE_CREATE', $userPermissions)): ?>
                            <button type="button" class="btn btn-primary rounded-3 btn-create-group">
                                <i class="fa-solid fa-plus me-1"></i> Registrar Grupo
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="groupsTable" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 150px;">Código</th>
                                        <th>Descripción del Grupo</th>
                                        <th style="width: 150px;">Estado</th>
                                        <th class="text-center" style="width: 120px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($groups as $group): ?>
                                        <tr id="group_row_<?= $group['id'] ?>">
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($group['code']) ?></td>
                                            <td><?= htmlspecialchars($group['description']) ?></td>
                                            <td>
                                                <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 <?= $group['status'] == 1 ? 'bg-success text-success' : 'bg-danger text-danger' ?>">
                                                    <?= $group['status'] == 1 ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (in_array('ROLE_EDIT', $userPermissions)): ?>
                                                        <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-group" data-id="<?= $group['id'] ?>" title="Editar">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (in_array('ROLE_DELETE', $userPermissions)): ?>
                                                        <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-group" data-id="<?= $group['id'] ?>" data-code="<?= htmlspecialchars($group['code']) ?>" title="Eliminar">
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

            <!-- Pestaña Subgrupos -->
            <div class="tab-pane fade" id="subgroups-pane" role="tabpanel" aria-labelledby="subgroups-tab" tabindex="0">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
                        <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-table-list me-2 text-secondary"></i> Catálogo de Subgrupos</h6>
                        <?php if (in_array('ROLE_CREATE', $userPermissions)): ?>
                            <button type="button" class="btn btn-primary rounded-3 btn-create-subgroup">
                                <i class="fa-solid fa-plus me-1"></i> Registrar Subgrupo
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="subgroupsTable" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 150px;">Código</th>
                                        <th>Descripción del Subgrupo</th>
                                        <th>Grupo Genérico (Padre)</th>
                                        <th style="width: 150px;">Estado</th>
                                        <th class="text-center" style="width: 120px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subgroups as $sub): ?>
                                        <tr id="subgroup_row_<?= $sub['id'] ?>">
                                            <td class="fw-bold text-primary"><?= htmlspecialchars($sub['code']) ?></td>
                                            <td><?= htmlspecialchars($sub['description']) ?></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($sub['group_name']) ?></td>
                                            <td>
                                                <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 <?= $sub['status'] == 1 ? 'bg-success text-success' : 'bg-danger text-danger' ?>">
                                                    <?= $sub['status'] == 1 ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (in_array('ROLE_EDIT', $userPermissions)): ?>
                                                        <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-subgroup" data-id="<?= $sub['id'] ?>" title="Editar">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (in_array('ROLE_DELETE', $userPermissions)): ?>
                                                        <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-subgroup" data-id="<?= $sub['id'] ?>" data-code="<?= htmlspecialchars($sub['code']) ?>" title="Eliminar">
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
    </div>
</div>

<!-- ==========================================================================
   MODAL PARA GRUPOS (REGISTRAR / EDITAR)
   ========================================================================== -->
<div class="modal fade" id="groupModal" tabindex="-1" aria-labelledby="groupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="groupModalLabel"><i class="fa-solid fa-folder text-primary me-2"></i> Registrar Grupo Genérico</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="groupForm" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="group_code" class="form-label small fw-semibold text-secondary">Código del Grupo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="group_code" name="code" placeholder="Ej. 74 (Uso de catálogo patrimonial)" required>
                    </div>
                    <div class="mb-3">
                        <label for="group_description" class="form-label small fw-semibold text-secondary">Descripción / Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="group_description" name="description" placeholder="Ej. EQUIPOS DE COMPUTO" required>
                    </div>
                    <div>
                        <label for="group_status" class="form-label small fw-semibold text-secondary">Estado</label>
                        <select class="form-select rounded-3" id="group_status" name="status">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveGroup">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================================================
   MODAL PARA SUBGRUPOS (REGISTRAR / EDITAR)
   ========================================================================== -->
<div class="modal fade" id="subgroupModal" tabindex="-1" aria-labelledby="subgroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="subgroupModalLabel"><i class="fa-solid fa-folder-tree text-primary me-2"></i> Registrar Subgrupo Específico</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="subgroupForm" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="subgroup_parent" class="form-label small fw-semibold text-secondary">Grupo Genérico (Padre) <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="subgroup_parent" name="group_id" required>
                            <option value="" disabled selected>Seleccione grupo genérico...</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['code']) ?> - <?= htmlspecialchars($g['description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subgroup_code" class="form-label small fw-semibold text-secondary">Código del Subgrupo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="subgroup_code" name="code" placeholder="Ej. 08 (Uso de catálogo patrimonial)" required>
                    </div>
                    <div class="mb-3">
                        <label for="subgroup_description" class="form-label small fw-semibold text-secondary">Descripción / Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="subgroup_description" name="description" placeholder="Ej. COMPUTADORAS PERSONALES" required>
                    </div>
                    <div>
                        <label for="subgroup_status" class="form-label small fw-semibold text-secondary">Estado</label>
                        <select class="form-select rounded-3" id="subgroup_status" name="status">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveSubgroup">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Subgrupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTables en español
        $('#groupsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        $('#subgroupsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        // Instancias de Modales
        const groupModal = new bootstrap.Modal(document.getElementById('groupModal'));
        const subgroupModal = new bootstrap.Modal(document.getElementById('subgroupModal'));

        const groupForm = document.getElementById('groupForm');
        const subgroupForm = document.getElementById('subgroupForm');

        let groupActionUrl = '';
        let subgroupActionUrl = '';

        // ==========================================================================
        // OPERACIONES DE GRUPOS
        // ==========================================================================

        // Nuevo Grupo
        $(document).on('click', '.btn-create-group', function() {
            document.getElementById('groupModalLabel').innerHTML = '<i class="fa-solid fa-folder text-primary me-2"></i> Registrar Grupo Genérico';
            groupForm.reset();
            document.getElementById('group_status').value = "1";
            groupActionUrl = `<?= BASE_URL ?>/api/groups/guardar`;
            groupModal.show();
        });

        // Editar Grupo
        $(document).on('click', '.btn-edit-group', function() {
            const id = $(this).data('id');
            document.getElementById('groupModalLabel').innerHTML = '<i class="fa-solid fa-pen text-primary me-2"></i> Editar Grupo Genérico';
            groupForm.reset();

            showLoader('Obteniendo información del grupo...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/groups/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const group = response.data;
                        document.getElementById('group_code').value = group.code;
                        document.getElementById('group_description').value = group.description;
                        document.getElementById('group_status').value = group.status;

                        groupActionUrl = `<?= BASE_URL ?>/api/groups/actualizar/${id}`;
                        groupModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información del grupo.', 'error');
                }
            });
        });

        // Guardar/Actualizar Grupo
        groupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            showLoader('Guardando cambios...');

            $.ajax({
                url: groupActionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        groupModal.hide();
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
                    let errorMsg = 'No se pudo guardar la información del grupo.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Eliminar Grupo
        $(document).on('click', '.btn-delete-group', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `El grupo genérico con código "${code}" será eliminado de la clasificación activa.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Eliminando grupo genérico...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/groups/eliminar/${id}`,
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

        // ==========================================================================
        // OPERACIONES DE SUBGRUPOS
        // ==========================================================================

        // Nuevo Subgrupo
        $(document).on('click', '.btn-create-subgroup', function() {
            document.getElementById('subgroupModalLabel').innerHTML = '<i class="fa-solid fa-folder-tree text-primary me-2"></i> Registrar Subgrupo Específico';
            subgroupForm.reset();
            document.getElementById('subgroup_parent').value = "";
            document.getElementById('subgroup_status').value = "1";
            subgroupActionUrl = `<?= BASE_URL ?>/api/subgroups/guardar`;
            subgroupModal.show();
        });

        // Editar Subgrupo
        $(document).on('click', '.btn-edit-subgroup', function() {
            const id = $(this).data('id');
            document.getElementById('subgroupModalLabel').innerHTML = '<i class="fa-solid fa-pen text-primary me-2"></i> Editar Subgrupo Específico';
            subgroupForm.reset();

            showLoader('Obteniendo información del subgrupo...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/subgroups/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const sub = response.data;
                        document.getElementById('subgroup_parent').value = sub.group_id;
                        document.getElementById('subgroup_code').value = sub.code;
                        document.getElementById('subgroup_description').value = sub.description;
                        document.getElementById('subgroup_status').value = sub.status;

                        subgroupActionUrl = `<?= BASE_URL ?>/api/subgroups/actualizar/${id}`;
                        subgroupModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información del subgrupo.', 'error');
                }
            });
        });

        // Guardar/Actualizar Subgrupo
        subgroupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            showLoader('Guardando cambios...');

            $.ajax({
                url: subgroupActionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        subgroupModal.hide();
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
                    let errorMsg = 'No se pudo guardar la información del subgrupo.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Eliminar Subgrupo
        $(document).on('click', '.btn-delete-subgroup', function() {
            const id = $(this).data('id');
            const code = $(this).data('code');

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `El subgrupo específico con código "${code}" será eliminado de la clasificación activa.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Eliminando subgrupo específico...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/subgroups/eliminar/${id}`,
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
