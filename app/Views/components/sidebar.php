<?php
$currentUri = $_SERVER['REQUEST_URI'];
$permissions = $userPermissions ?? [];

function isActive($path, $currentUri) {
    // Si la ruta del enlace coincide o está contenida, marcar como activa
    $basePath = parse_url($currentUri, PHP_URL_PATH);
    // Eliminar el prefijo del proyecto en subcarpeta
    $basePath = str_replace('/invent', '', $basePath);
    if ($path === '/' && ($basePath === '/' || $basePath === '')) {
        return 'active';
    }
    if ($path !== '/' && strpos($basePath, $path) === 0) {
        return 'active';
    }
    return '';
}

function hasPerm($code, $permissions) {
    return in_array($code, $permissions);
}
?>
<aside class="sidebar">
    <div class="sidebar-logo d-flex align-items-center" style="padding: 15px 20px; border-bottom: 1px solid var(--border-color); height: 70px;">
        <i class="fa-solid fa-boxes-stacked fa-lg logo-icon" style="color: var(--primary-color); min-width: 24px;"></i>
        <div class="d-flex flex-column align-items-start logo-text ms-2" style="flex-grow: 1;">
            <span style="font-size: 1.1rem; font-weight: 700; color: #111; letter-spacing: 0.5px; line-height: 1.2;">EPS RIOJA</span>
            <span class="text-muted fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.8px; margin-top: 2px;">Inventario</span>
        </div>
    </div>
    
    <nav class="sidebar-menu">
        <!-- Dashboard Principal -->
        <?php if (hasPerm('DASHBOARD_VIEW', $permissions)): ?>
            <a href="<?= BASE_URL ?>/" class="sidebar-link <?= isActive('/', $currentUri) ?>">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>
        
        <!-- Sección de Operaciones de Bienes -->
        <?php if (hasPerm('ASSET_VIEW', $permissions) || hasPerm('INVENTORY_VIEW', $permissions)): ?>
            <div class="sidebar-menu-title">Operaciones</div>
            
            <?php if (hasPerm('ASSET_VIEW', $permissions)): ?>
                <a href="<?= BASE_URL ?>/bienes" class="sidebar-link <?= isActive('/bienes', $currentUri) ?>">
                    <i class="fa-solid fa-laptop"></i>
                    <span>Bienes Patrimoniales</span>
                </a>
            <?php endif; ?>
            
            <?php if (hasPerm('INVENTORY_VIEW', $permissions)): ?>
                <a href="<?= BASE_URL ?>/inventories" class="sidebar-link <?= isActive('/inventories', $currentUri) ?>">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Inventario Físico</span>
                </a>
            <?php endif; ?>
            
            <?php if (hasPerm('REPORT_VIEW', $permissions)): ?>
                <a href="<?= BASE_URL ?>/reports" class="sidebar-link <?= isActive('/reports', $currentUri) ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Reportes Analíticos</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Configuración y Parametrización -->
        <?php if (hasPerm('ROLE_VIEW', $permissions) || hasPerm('USER_VIEW', $permissions)): ?>
            <div class="sidebar-menu-title">Configuración</div>
            
            <?php if (hasPerm('ROLE_VIEW', $permissions)): ?>
                <a href="<?= BASE_URL ?>/roles" class="sidebar-link <?= isActive('/roles', $currentUri) ?>">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Roles y Permisos</span>
                </a>
                <a href="<?= BASE_URL ?>/groups" class="sidebar-link <?= isActive('/groups', $currentUri) ?>">
                    <i class="fa-solid fa-folder-tree"></i>
                    <span>Grupos y Subgrupos</span>
                </a>
                <a href="<?= BASE_URL ?>/responsibles" class="sidebar-link <?= isActive('/responsibles', $currentUri) ?>">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Responsables</span>
                </a>
                <a href="<?= BASE_URL ?>/offices" class="sidebar-link <?= isActive('/offices', $currentUri) ?>">
                    <i class="fa-solid fa-building"></i>
                    <span>Oficinas / Áreas</span>
                </a>
                <a href="<?= BASE_URL ?>/funding-sources" class="sidebar-link <?= isActive('/funding-sources', $currentUri) ?>">
                    <i class="fa-solid fa-coins"></i>
                    <span>Fuentes de Financiamiento</span>
                </a>
                <a href="<?= BASE_URL ?>/locations" class="sidebar-link <?= isActive('/locations', $currentUri) ?>">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Locales / Sedes</span>
                </a>
            <?php endif; ?>
            
            <?php if (hasPerm('USER_VIEW', $permissions)): ?>
                <a href="<?= BASE_URL ?>/users" class="sidebar-link <?= isActive('/users', $currentUri) ?>">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Usuarios</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
    
    <!-- Footer del Sidebar con info de sesión rápida -->
    <div class="p-3 border-top border-light">
        <a href="<?= BASE_URL ?>/logout" class="sidebar-link text-danger m-0">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>
