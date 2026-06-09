<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark m-0">Usuarios de la Plataforma</h5>
            <p class="text-muted small m-0">Administre las cuentas de usuario de la entidad, asigne roles y controle el estado de sus accesos.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-users-gear me-2 text-secondary"></i> Catálogo de Usuarios</h6>
                <?php if (in_array('USER_CREATE', $userPermissions)): ?>
                    <button type="button" class="btn btn-primary rounded-3 btn-create-user">
                        <i class="fa-solid fa-user-plus me-1"></i> Registrar Usuario
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="usersTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol asignado</th>
                                <th>Último Acceso</th>
                                <th style="width: 100px;">Estado</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr id="user_row_<?= $user['id'] ?>">
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($user['full_name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-secondary">@<?= htmlspecialchars($user['username']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary py-1.5 px-2.5 rounded-3">
                                            <?= htmlspecialchars($user['role_name']) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8
                                            <?php 
                                                if ($user['status'] == 1) echo 'bg-success text-success';
                                                elseif ($user['status'] == 2) echo 'bg-danger text-danger';
                                                else echo 'bg-secondary text-secondary';
                                            ?>">
                                            <?php 
                                                if ($user['status'] == 1) echo 'Activo';
                                                elseif ($user['status'] == 2) echo 'Bloqueado';
                                                else echo 'Inactivo';
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php if (in_array('USER_EDIT', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-primary rounded-3 btn-edit-user" data-id="<?= $user['id'] ?>" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array('USER_DELETE', $userPermissions)): ?>
                                                <button type="button" class="btn btn-sm btn-light text-danger rounded-3 btn-delete-user" data-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['full_name']) ?>" title="Eliminar">
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
   MODAL PARA USUARIOS (REGISTRAR / EDITAR)
   ========================================================================== -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="userForm" autocomplete="off" class="modal-content border-0 shadow-lg rounded-4">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark m-0" id="userModalLabel"><i class="fa-solid fa-user-plus text-primary me-2"></i> Registrar Usuario</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="user_fullname" class="form-label small fw-semibold text-secondary">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3" id="user_fullname" name="full_name" placeholder="Ej. Juan Pérez Medina" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_username" class="form-label small fw-semibold text-secondary">Nombre de Usuario <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">@</span>
                            <input type="text" class="form-control rounded-end-3" id="user_username" name="username" placeholder="juan.perez" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="user_email" class="form-label small fw-semibold text-secondary">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3" id="user_email" name="email" placeholder="juan@correo.com" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_role" class="form-label small fw-semibold text-secondary">Rol de Acceso <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="user_role" name="role_id" required>
                            <option value="" disabled selected>Seleccione rol...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="user_status" class="form-label small fw-semibold text-secondary">Estado</label>
                        <select class="form-select rounded-3" id="user_status" name="status">
                            <option value="1">Activo</option>
                            <option value="2">Bloqueado</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="user_password" class="form-label small fw-semibold text-secondary" id="passwordLabel">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control rounded-3" id="user_password" name="password" placeholder="Ingrese contraseña">
                    <div class="text-muted fs-7 mt-1" id="passwordHelp">La contraseña debe ser segura y fácil de recordar.</div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSaveUser">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ID de usuario en sesión actual (para validaciones de seguridad en frontend)
        const currentUserId = <?= json_encode(\App\Core\Session::get('user_id')) ?>;

        // Inicializar DataTable en español
        $('#usersTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        // Instancias de modal y formulario
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));
        const userForm = document.getElementById('userForm');
        let actionUrl = '';

        // Botón: Registrar Usuario
        $(document).on('click', '.btn-create-user', function() {
            document.getElementById('userModalLabel').innerHTML = '<i class="fa-solid fa-user-plus text-primary me-2"></i> Registrar Nuevo Usuario';
            userForm.reset();
            
            // Configurar campos específicos de creación
            document.getElementById('user_role').value = "";
            document.getElementById('user_status').value = "1";
            document.getElementById('user_password').required = true;
            document.getElementById('passwordLabel').innerHTML = 'Contraseña <span class="text-danger">*</span>';
            document.getElementById('passwordHelp').innerHTML = 'Ingrese una contraseña segura para el nuevo usuario.';
            
            actionUrl = `<?= BASE_URL ?>/api/users/guardar`;
            userModal.show();
        });

        // Botón: Editar Usuario
        $(document).on('click', '.btn-edit-user', function() {
            const id = $(this).data('id');
            document.getElementById('userModalLabel').innerHTML = '<i class="fa-solid fa-pen text-primary me-2"></i> Editar Cuenta de Usuario';
            userForm.reset();

            // Configurar campos específicos de edición
            document.getElementById('user_password').required = false;
            document.getElementById('passwordLabel').innerHTML = 'Contraseña (Opcional)';
            document.getElementById('passwordHelp').innerHTML = 'Deje este campo en blanco si no desea cambiar la contraseña actual.';

            showLoader('Obteniendo información del usuario...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/users/detalle/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const user = response.data;
                        document.getElementById('user_fullname').value = user.full_name;
                        document.getElementById('user_username').value = user.username;
                        document.getElementById('user_email').value = user.email;
                        document.getElementById('user_role').value = user.role_id;
                        document.getElementById('user_status').value = user.status;

                        actionUrl = `<?= BASE_URL ?>/api/users/actualizar/${id}`;
                        userModal.show();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoader();
                    Swal.fire('Error', 'No se pudo cargar la información del usuario.', 'error');
                }
            });
        });

        // Formulario: Guardar / Actualizar
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            showLoader('Guardando cambios en la cuenta...');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        userModal.hide();
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
                    let errorMsg = 'No se pudo guardar la información del usuario.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Botón: Eliminar Usuario
        $(document).on('click', '.btn-delete-user', function() {
            const id = parseInt($(this).data('id'));
            const name = $(this).data('name');

            // Validación de seguridad frontend: No eliminarse a sí mismo
            if (id === parseInt(currentUserId)) {
                Swal.fire('Acción Denegada', 'No puede dar de baja su propia cuenta de usuario en sesión.', 'error');
                return;
            }

            Swal.fire({
                title: '¿Está seguro de eliminar?',
                text: `El usuario "${name}" será dado de baja del catálogo activo de cuentas.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader('Dando de baja al usuario...');
                    $.ajax({
                        url: `<?= BASE_URL ?>/api/users/eliminar/${id}`,
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
                                    title: 'Usuario Eliminado',
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
                            let errorMsg = 'No se pudo procesar la baja del usuario.';
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
