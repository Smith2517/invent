<!-- Importar Chart.js para inicialización de gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Fila de Banner de Bienvenida -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white border-0 overflow-hidden shadow-sm position-relative" style="border-radius: 16px;">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center position-relative z-index-1">
                    <div class="col-lg-7">
                        <h2 class="fw-bold mb-2">¡Hola, <?= htmlspecialchars($currentUserFullName) ?>!</h2>
                        <p class="mb-4 opacity-75">Bienvenido al panel analítico de control patrimonial de EPS RIOJA. Monitorea estados físicos, clasificaciones y ubicaciones en tiempo real.</p>
                        <a href="<?= BASE_URL ?>/bienes" class="btn btn-light text-primary fw-medium px-4 py-2 rounded-3">
                            <i class="fa-solid fa-laptop me-2"></i> Ir a Bienes Patrimoniales
                        </a>
                    </div>
                </div>
            </div>
            <!-- Círculos decorativos de fondo -->
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

<!-- Fila de Gráficos de Estado y Tiempo -->
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
                <h6 class="fw-bold text-dark m-0">Registro de Bienes por Mes (Último Año)</h6>
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

<!-- Fila de Distribución por Oficina y Grupos -->
<div class="row">
    <!-- Bienes por Oficina/Área -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 pb-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-building text-primary me-2"></i> Distribución por Oficinas / Áreas</h6>
                <p class="text-muted small m-0">Top 5 áreas con mayor cantidad de bienes bajo custodia.</p>
            </div>
            <div class="card-body">
                <?php if (empty($assetsByOffice)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="fa-solid fa-building-circle-exclamation fa-2x text-muted opacity-50 mb-2"></i>
                        <div>Sin registros de oficinas</div>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($assetsByOffice as $office): ?>
                            <?php 
                            $pct = ($stats['total_assets'] > 0) ? round(($office['count'] / $stats['total_assets']) * 100, 1) : 0;
                            ?>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark small text-truncate" style="max-width: 70%;" title="<?= htmlspecialchars($office['label']) ?>"><?= htmlspecialchars($office['label']) ?></span>
                                    <span class="text-muted small fw-bold"><?= $office['count'] ?> bienes (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bienes por Grupo Genérico -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 pb-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-folder-tree text-success me-2"></i> Distribución por Grupos Genéricos</h6>
                <p class="text-muted small m-0">Top 5 grupos principales de bienes patrimoniales.</p>
            </div>
            <div class="card-body">
                <?php if (empty($assetsByGroup)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="fa-solid fa-folder-open fa-2x text-muted opacity-50 mb-2"></i>
                        <div>Sin registros de grupos genéricos</div>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($assetsByGroup as $group): ?>
                            <?php 
                            $pct = ($stats['total_assets'] > 0) ? round(($group['count'] / $stats['total_assets']) * 100, 1) : 0;
                            ?>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark small text-truncate" style="max-width: 70%;" title="<?= htmlspecialchars($group['label']) ?>"><?= htmlspecialchars($group['label']) ?></span>
                                    <span class="text-muted small fw-bold"><?= $group['count'] ?> bienes (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                    backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545', '#6c757d'],
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
