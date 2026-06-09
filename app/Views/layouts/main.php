<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'INVENTARIO - EPS RIOJA' ?></title>
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="<?= BASE_URL ?>/public/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <script>
        // Aplicar estado colapsado de inmediato para evitar parpadeos
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.body.classList.add('collapsed-sidebar');
        }
    </script>

    <!-- Incluir Barra de Navegación Lateral -->
    <?php include ROOT_DIR . '/app/Views/components/sidebar.php'; ?>

    <!-- Fondo Oscuro para Menú Móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Encabezado Principal -->
    <header class="main-header">
        <div class="d-flex align-items-center">
            <!-- Botón Colapsar/Expandir en Escritorio -->
            <button class="btn btn-link text-dark p-0 me-3 d-none d-md-inline-block shadow-none" id="sidebarCollapseBtn" title="Contraer/Expandir menú">
                <i class="fa-solid fa-bars fa-lg"></i>
            </button>
            <!-- Botón Abrir Menú en Móvil -->
            <button class="btn btn-link text-dark p-0 me-3 d-md-none shadow-none" id="mobileSidebarToggle" title="Abrir menú">
                <i class="fa-solid fa-bars fa-lg"></i>
            </button>
            <h5 class="fw-bold text-dark mb-0 page-layout-title"><?= $title ?? 'Inicio' ?></h5>
        </div>

        <div class="header-actions">
            <!-- Selector Perfil de Usuario -->
            <div class="dropdown">
                <a href="#" class="user-profile dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($currentUserFullName ?? 'Admin') ?>&background=0d6efd&color=fff" class="user-avatar" alt="Avatar">
                    <div class="d-none d-md-block text-start">
                        <div class="fw-semibold small text-dark lh-1"><?= htmlspecialchars($currentUserFullName ?? '') ?></div>
                        <span class="text-muted small fs-7"><?= htmlspecialchars($currentUserRole ?? '') ?></span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3 mt-2" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/logout"><i class="fa-solid fa-right-from-bracket me-2 text-danger"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Contenedor Principal de Contenido -->
    <main class="main-content">
        <?= $content ?>
    </main>

    <!-- jQuery & Bootstrap 5 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Global AJAX Loader Overlay -->
    <div id="loading-overlay" class="loading-overlay d-none">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Procesando...</span>
            </div>
            <div class="mt-3 fw-bold text-dark text-center" id="loading-message">Procesando solicitud...</div>
        </div>
    </div>

    <!-- Script de Notificaciones Flash de SweetAlert2, Loader y Control de Inactividad -->
    <script>
        // Funciones del Loader Global
        window.showLoader = function(message) {
            const overlay = document.getElementById('loading-overlay');
            const msgEl = document.getElementById('loading-message');
            if (overlay) {
                if (msgEl && message) {
                    msgEl.textContent = message;
                } else if (msgEl) {
                    msgEl.textContent = "Procesando solicitud...";
                }
                overlay.classList.remove('d-none');
            }
        };

        window.hideLoader = function() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.add('d-none');
            }
        };

        $(document).ready(function() {
            // Control de colapso de Sidebar (Escritorio)
            $('#sidebarCollapseBtn').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('collapsed-sidebar');
                const isCollapsed = $('body').hasClass('collapsed-sidebar');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
            });

            // Control de apertura de Sidebar (Móvil)
            $('#mobileSidebarToggle').on('click', function(e) {
                e.preventDefault();
                $('.sidebar').addClass('mobile-show');
                $('#sidebarOverlay').addClass('active');
            });

            // Control de cierre de Sidebar (Móvil) al hacer clic en el overlay
            $('#sidebarOverlay').on('click', function(e) {
                $('.sidebar').removeClass('mobile-show');
                $(this).removeClass('active');
            });

            // Cerrar menú móvil al hacer clic en cualquier enlace
            $('.sidebar-link').on('click', function() {
                $('.sidebar').removeClass('mobile-show');
                $('#sidebarOverlay').removeClass('active');
            });

            <?php if (\App\Core\Session::hasFlash('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '<?= \App\Core\Session::getFlash('success') ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            <?php endif; ?>

            <?php if (\App\Core\Session::hasFlash('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?= \App\Core\Session::getFlash('error') ?>',
                    confirmButtonColor: '#0d6efd'
                });
            <?php endif; ?>
        });

        // Temporizador de Inactividad de Sesión (45 minutos)
        <?php if (\App\Core\Session::has('user_id')): ?>
        (function() {
            const timeoutSeconds = <?= SESSION_TIMEOUT ?>; // 2700
            let idleTime = 0;

            const resetTimer = () => {
                idleTime = 0;
            };

            // Eventos que denotan actividad
            const events = ['mousemove', 'keypress', 'mousedown', 'touchstart', 'scroll'];
            events.forEach(event => {
                document.addEventListener(event, resetTimer, { passive: true });
            });

            // Cada segundo incrementamos el contador
            const interval = setInterval(() => {
                idleTime++;
                if (idleTime >= timeoutSeconds) {
                    clearInterval(interval);
                    window.location.href = '<?= BASE_URL ?>/logout?reason=timeout';
                }
            }, 1000);
        })();
        <?php endif; ?>
    </script>
</body>
</html>
