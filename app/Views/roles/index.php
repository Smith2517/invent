<div class="row mb-4">
    <div class="col-12">
        <h5 class="fw-bold text-dark m-0">Roles y Matriz de Permisos</h5>
        <p class="text-muted small m-0">Administre los niveles de acceso y los permisos granulares asignados a los usuarios del sistema.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 bg-transparent py-3 px-4">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-shield-halved me-2 text-secondary"></i> Catálogo de Roles</h6>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="rolesTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nombre del Rol</th>
                                <th>Descripción</th>
                                <th style="width: 150px;">Estado</th>
                                <th style="width: 150px;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                                <tr id="role_row_<?= $role['id'] ?>">
                                    <td class="fw-semibold">#<?= $role['id'] ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($role['name']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($role['description'] ?? 'Sin descripción') ?></td>
                                    <td>
                                        <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 <?= $role['status'] == 1 ? 'bg-success text-success' : 'bg-danger text-danger' ?>">
                                            <?= $role['status'] == 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 btn-edit-permissions" data-id="<?= $role['id'] ?>" data-name="<?= htmlspecialchars($role['name']) ?>">
                                            <i class="fa-solid fa-shield-halved me-1"></i> Permisos
                                        </button>
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
   MODAL PARA MATRIZ DE PERMISOS DE ROLES
   ========================================================================== -->
<div class="modal fade" id="rolePermissionsModal" tabindex="-1" aria-labelledby="rolePermissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="rolePermissionsForm" autocomplete="off" class="modal-content border-0 shadow-lg rounded-4">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="modal-header border-bottom py-3 px-4">
                <div>
                    <h6 class="modal-title fw-bold text-dark m-0" id="rolePermissionsModalLabel"><i class="fa-solid fa-shield-halved text-primary me-2"></i> Permisos de Rol</h6>
                    <p class="text-muted small m-0" id="rolePermissionsModalSub">Cargando permisos...</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Botones de selección rápida -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-light">
                    <span class="text-secondary small fw-semibold">Marque los accesos para habilitar en el rol:</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" id="selectAll" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-check-double me-1"></i> Seleccionar Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1 ms-1" id="deselectAll" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-xmark me-1"></i> Limpiar Selección
                        </button>
                    </div>
                </div>
                <!-- Contenedor dinámico de permisos por módulo -->
                <div id="permissionsContainer"></div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSavePermissions">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Permisos
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // DataTable en español
        $('#rolesTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            paging: false,
            searching: false,
            info: false,
            order: []
        });

        // Instancia de Modal
        const rolePermissionsModal = new bootstrap.Modal(document.getElementById('rolePermissionsModal'));
        const rolePermissionsForm = document.getElementById('rolePermissionsForm');
        let updateUrl = '';

        // Botón: Editar Permisos de Rol
        $(document).on('click', '.btn-edit-permissions', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            document.getElementById('rolePermissionsModalLabel').innerHTML = `<i class="fa-solid fa-shield-halved text-primary me-2"></i> Permisos de Rol: ${name}`;
            document.getElementById('rolePermissionsModalSub').innerHTML = 'Configurando matriz de permisos institucionales';
            document.getElementById('permissionsContainer').innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="text-muted small mt-2 m-0">Obteniendo matriz de accesos...</p></div>';

            updateUrl = `<?= BASE_URL ?>/api/roles/actualizar/${id}`;
            rolePermissionsModal.show();

            $.ajax({
                url: `<?= BASE_URL ?>/api/roles/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        let html = '<div class="row">';
                        
                        for (const module in data.permissionsByModule) {
                            const perms = data.permissionsByModule[module];
                            html += `
                            <div class="col-md-6 mb-4">
                                <div class="card border border-light shadow-xs h-100 rounded-3">
                                    <div class="card-header bg-light border-0 py-2.5">
                                        <h6 class="fw-bold text-dark m-0 d-flex align-items-center small">
                                            <i class="fa-solid fa-cubes me-2 text-primary"></i>
                                            ${module}
                                        </h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="d-flex flex-column gap-2">`;
                            
                            perms.forEach(p => {
                                const checked = data.assignedPermissionIds.includes(parseInt(p.id)) ? 'checked' : '';
                                html += `
                                <div class="form-check form-switch">
                                    <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="${p.id}" id="perm_${p.id}" ${checked}>
                                    <label class="form-check-label text-dark small" for="perm_${p.id}">
                                        <strong>${p.code}</strong>
                                        <div class="text-muted" style="font-size: 0.75rem; line-height: 1.25;">${p.description}</div>
                                    </label>
                                </div>`;
                            });

                            html += `
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        }

                        html += '</div>';
                        document.getElementById('permissionsContainer').innerHTML = html;
                    } else {
                        rolePermissionsModal.hide();
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    rolePermissionsModal.hide();
                    Swal.fire('Error', 'No se pudo cargar la matriz de permisos.', 'error');
                }
            });
        });

        // Botones de Selección Rápida en el Modal
        document.getElementById('selectAll').addEventListener('click', function() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
        });

        document.getElementById('deselectAll').addEventListener('click', function() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
        });

        // Formulario: Guardar Permisos
        rolePermissionsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            showLoader('Actualizando matriz de accesos...');

            $.ajax({
                url: updateUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        rolePermissionsModal.hide();
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
                    let errorMsg = 'No se pudo actualizar la configuración de accesos.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });
    });
</script>
