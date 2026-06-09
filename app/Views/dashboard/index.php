<!-- Fila de Banner de Bienvenida -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white border-0 overflow-hidden shadow-sm position-relative" style="border-radius: 16px;">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center position-relative z-index-1">
                    <div class="col-lg-7">
                        <h2 class="fw-bold mb-2">¡Hola, <?= htmlspecialchars($currentUserFullName) ?>!</h2>
                        <p class="mb-4 opacity-75">Bienvenido al panel analítico de control patrimonial. Monitorea altas, bajas, reasignaciones y estados físicos en tiempo real.</p>
                        <a href="<?= BASE_URL ?>/bienes/crear" class="btn btn-light text-primary fw-medium px-4 py-2 rounded-3">
                            <i class="fa-solid fa-plus me-2"></i> Registrar Nuevo Bien
                        </a>
                    </div>
                </div>
            </div>
            <!-- Círculos decorativos de fondo estilo Flexy -->
            <div class="position-absolute end-0 bottom-0 opacity-10 translate-middle-y me-5 d-none d-lg-block">
                <i class="fa-solid fa-boxes-stacked" style="font-size: 10rem;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Tarjetas KPI -->
<div class="row mb-4">
    <!-- Bienes Totales -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body kpi-card">
                <div>
                    <span class="text-muted small fw-medium">Bienes Totales</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= $stats['total_assets'] ?></h3>
                </div>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuarios del Sistema -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body kpi-card">
                <div>
                    <span class="text-muted small fw-medium">Usuarios Activos</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= $stats['total_users'] ?></h3>
                </div>
                <div class="kpi-icon bg-info bg-opacity-10 text-info">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsables Custodios -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body kpi-card">
                <div>
                    <span class="text-muted small fw-medium">Responsables Asignados</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= $stats['total_responsibles'] ?></h3>
                </div>
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bienes Verificados -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body kpi-card">
                <div>
                    <span class="text-muted small fw-medium">Bienes Verificados</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0"><?= $stats['verified_assets'] ?></h3>
                </div>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Gráficos -->
<div class="row mb-4">
    <!-- Gráfico de Estado Físico -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark m-0">Estado Físico de Bienes</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <?php if (empty($assetsByStatus)): ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-chart-pie fa-3x text-muted opacity-50 mb-3"></i>
                        <p class="text-muted small m-0">Sin datos de bienes registrados</p>
                    </div>
                <?php else: ?>
                    <div style="position: relative; height:220px; width:220px">
                        <canvas id="chartStatus"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráfico de Altas Mensuales -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 pb-0">
                <h6 class="fw-bold text-dark m-0">Registro de Bienes por Mes</h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <?php if (empty($monthlyEntries)): ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-chart-column fa-3x text-muted opacity-50 mb-3"></i>
                        <p class="text-muted small m-0">Sin registros en el último año</p>
                    </div>
                <?php else: ?>
                    <div style="position: relative; height:220px; width:100%">
                        <canvas id="chartMonthly"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Fila Inferior: Bitácora y Accesos Rápidos -->
<div class="row">
    <!-- Últimas Actividades (Bitácora de Auditoría) -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 pb-0">
                <h6 class="fw-bold text-dark m-0">Bitácora de Auditoría Reciente</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Módulo</th>
                                <th>Fecha/Hora</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLogs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        No hay registros de actividad recientes.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($log['user_fullname'] ?? 'Sistema') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8
                                                <?php
                                                    if ($log['action'] === 'CREATE') echo 'bg-success text-success';
                                                    elseif ($log['action'] === 'UPDATE') echo 'bg-primary text-primary';
                                                    elseif ($log['action'] === 'DELETE') echo 'bg-danger text-danger';
                                                    else echo 'bg-secondary text-secondary';
                                                ?>">
                                                <?= $log['action'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log['module']) ?></td>
                                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                        <td class="small font-monospace"><?= htmlspecialchars($log['ip_address']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Accesos Rápidos -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-0 pb-0">
                <h6 class="fw-bold text-dark m-0">Enlaces Rápidos</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="<?= BASE_URL ?>/bienes" class="btn btn-light text-start py-3 px-3 rounded-3 d-flex align-items-center justify-content-between shadow-xs">
                        <div>
                            <div class="fw-bold text-dark">Gestión de Bienes</div>
                            <span class="text-muted small fs-7">Ver, editar y eliminar bienes</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-primary"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/inventories" class="btn btn-light text-start py-3 px-3 rounded-3 d-flex align-items-center justify-content-between shadow-xs">
                        <div>
                            <div class="fw-bold text-dark">Inventario Físico</div>
                            <span class="text-muted small fs-7">Verificación directa de bienes patrimoniales</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-success"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/roles" class="btn btn-light text-start py-3 px-3 rounded-3 d-flex align-items-center justify-content-between shadow-xs">
                        <div>
                            <div class="fw-bold text-dark">Roles del Sistema</div>
                            <span class="text-muted small fs-7">Administrar niveles de acceso</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-warning"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de Inicialización de Gráficos con Chart.js -->
<?php if (!empty($assetsByStatus) || !empty($monthlyEntries)): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($assetsByStatus)): ?>
        // Gráfico de Estado Físico
        const ctxStatus = document.getElementById('chartStatus').getContext('2d');
        const statusData = <?= json_encode($assetsByStatus) ?>;
        
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.label),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { family: 'Poppins', size: 11 }
                        }
                    }
                },
                cutout: '70%'
            }
        });
        <?php endif; ?>

        <?php if (!empty($monthlyEntries)): ?>
        // Gráfico de Altas Mensuales
        const ctxMonthly = document.getElementById('chartMonthly').getContext('2d');
        const monthlyData = <?= json_encode($monthlyEntries) ?>;

        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: 'Bienes Registrados',
                    data: monthlyData.map(item => item.count),
                    backgroundColor: '#0d6efd',
                    borderRadius: 5,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>
<?php endif; ?>
