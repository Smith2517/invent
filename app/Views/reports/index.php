<div class="row mb-4">
    <div class="col-12">
        <h5 class="fw-bold text-dark m-0">Reportes Analíticos Detallados</h5>
        <p class="text-muted small m-0">Consulte el patrimonio institucional mediante búsquedas cruzadas y analice distribuciones físicas e históricas.</p>
    </div>
</div>

<!-- Tarjeta de Filtros de Búsqueda Avanzada -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 border-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-filter me-2 text-secondary"></i> Filtros de Búsqueda Patrimonial</h6>
            </div>
            <div class="card-body pt-0">
                <form id="reportsFilterForm" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="row">
                        <!-- Buscador por texto general -->
                        <div class="col-md-4 mb-3">
                            <label for="filter_search" class="form-label small fw-semibold text-secondary">Búsqueda General</label>
                            <input type="text" class="form-control rounded-3" id="filter_search" name="search_text" placeholder="Código, tipo de bien, marca, modelo o serie">
                        </div>

                        <!-- Filtro por Año -->
                        <div class="col-md-2 mb-3">
                            <label for="filter_year" class="form-label small fw-semibold text-secondary">Año de Ingreso</label>
                            <select class="form-select rounded-3" id="filter_year" name="year">
                                <option value="" selected>Todos los años</option>
                                <?php foreach ($years as $yr): ?>
                                    <option value="<?= $yr ?>"><?= $yr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Rango de Fechas - Desde -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_from" class="form-label small fw-semibold text-secondary">Fecha Ingreso Desde</label>
                            <input type="date" class="form-control rounded-3" id="filter_from" name="date_from">
                        </div>

                        <!-- Rango de Fechas - Hasta -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_to" class="form-label small fw-semibold text-secondary">Fecha Ingreso Hasta</label>
                            <input type="date" class="form-control rounded-3" id="filter_to" name="date_to">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Oficina/Área -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_office" class="form-label small fw-semibold text-secondary">Oficina / Área</label>
                            <select class="form-select rounded-3" id="filter_office" name="office_id">
                                <option value="" selected>Todas las oficinas</option>
                                <?php foreach ($offices as $office): ?>
                                    <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Local / Sede -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_location" class="form-label small fw-semibold text-secondary">Local / Sede</label>
                            <select class="form-select rounded-3" id="filter_location" name="location_id">
                                <option value="" selected>Todos los locales</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Responsable -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_responsible" class="form-label small fw-semibold text-secondary">Responsable de Custodio</label>
                            <select class="form-select rounded-3" id="filter_responsible" name="responsible_id">
                                <option value="" selected>Todos los responsables</option>
                                <?php foreach ($responsibles as $resp): ?>
                                    <option value="<?= $resp['id'] ?>"><?= htmlspecialchars($resp['surnames'] . ', ' . $resp['names']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Grupo Genérico -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_group" class="form-label small fw-semibold text-secondary">Grupo Genérico</label>
                            <select class="form-select rounded-3" id="filter_group" name="group_id">
                                <option value="" selected>Todos los grupos</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['code']) ?> - <?= htmlspecialchars($g['description']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fuente de Financiamiento -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_funding" class="form-label small fw-semibold text-secondary">Fuente de Financiamiento</label>
                            <select class="form-select rounded-3" id="filter_funding" name="funding_source_id">
                                <option value="" selected>Todas las fuentes</option>
                                <?php foreach ($fundingSources as $fund): ?>
                                    <option value="<?= $fund['id'] ?>"><?= htmlspecialchars($fund['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Estado Físico -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_status" class="form-label small fw-semibold text-secondary">Estado de Conservación</label>
                            <select class="form-select rounded-3" id="filter_status" name="asset_status">
                                <option value="" selected>Todos los estados</option>
                                <option value="Bueno">Bueno</option>
                                <option value="Regular">Regular</option>
                                <option value="Malo">Malo</option>
                                <option value="Chatarra">Chatarra</option>
                            </select>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="col-md-6 mb-3 d-flex align-items-end justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-3 px-4" id="btnClearFilters">
                                <i class="fa-solid fa-eraser me-2"></i> Limpiar Filtros
                            </button>
                            <button type="submit" class="btn btn-primary rounded-3 px-5">
                                <i class="fa-solid fa-magnifying-glass me-2"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Resumen Estadístico (KPIs en caliente) -->
<div class="row mb-4 d-none" id="statsRow">
    <!-- Total Encontrados -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body kpi-card">
                <div>
                    <span class="text-muted small fw-medium">Coincidencias</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0" id="stat_total">0</h3>
                </div>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bien Más Antiguo -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body kpi-card">
                <div style="max-width: 80%;">
                    <span class="text-muted small fw-medium">Bien más Antiguo</span>
                    <div class="fw-bold text-dark mt-1 text-truncate small" id="stat_oldest_name">N/A</div>
                    <div class="text-secondary small mt-0.5 fs-7" id="stat_oldest_date">Ingreso: N/A</div>
                </div>
                <div class="kpi-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fa-solid fa-hourglass-end"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bien Más Nuevo -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body kpi-card">
                <div style="max-width: 80%;">
                    <span class="text-muted small fw-medium">Bien más Reciente</span>
                    <div class="fw-bold text-dark mt-1 text-truncate small" id="stat_newest_name">N/A</div>
                    <div class="text-secondary small mt-0.5 fs-7" id="stat_newest_date">Ingreso: N/A</div>
                </div>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de conservación -->
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center p-3">
                <span class="text-muted small fw-medium mb-2 d-block">Resumen de Conservación</span>
                <div class="d-flex flex-column gap-1.5" id="stat_conservation">
                    <!-- Dinámico -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados Filtrados -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm d-none" id="resultsCard">
            <div class="card-header bg-white py-3 px-4 border-0">
                <h6 class="fw-bold text-dark m-0"><i class="fa-solid fa-table-list me-2 text-secondary"></i> Bienes Patrimoniales Encontrados</h6>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="resultsTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">Código</th>
                                <th>Tipo de Bien</th>
                                <th>Marca / Modelo</th>
                                <th style="width: 130px;">Fecha Ingreso</th>
                                <th>Oficina / Área</th>
                                <th>Sede</th>
                                <th>Custodio</th>
                                <th style="width: 100px;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dinámico por AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar DataTable vacío
        const resultsTable = $('#resultsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: []
        });

        const filterForm = document.getElementById('reportsFilterForm');

        // Botón: Limpiar Filtros
        document.getElementById('btnClearFilters').addEventListener('click', function() {
            filterForm.reset();
            // Limpiar tabla e inactivar paneles
            resultsTable.clear().draw();
            document.getElementById('statsRow').classList.add('d-none');
            document.getElementById('resultsCard').classList.add('d-none');
        });

        // Formulario de Búsqueda
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            showLoader('Generando reporte patrimonial...');

            $.ajax({
                url: `<?= BASE_URL ?>/api/reports/buscar`,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        const data = response.data;
                        const assets = data.assets;
                        const stats = data.stats;

                        // 1. Rellenar Tabla
                        resultsTable.clear();
                        
                        assets.forEach(asset => {
                            const badgeClass = getBadgeClass(asset.asset_status);
                            const statusBadge = `<span class="badge bg-opacity-10 py-1.5 px-2.5 rounded-pill fs-8 ${badgeClass}">${asset.asset_status}</span>`;
                            const cleanBrand = asset.brand || 'N/A';
                            const cleanModel = asset.model || 'N/A';

                            resultsTable.row.add([
                                `<span class="fw-bold text-primary">${asset.custom_code}</span>`,
                                `<span class="fw-semibold text-dark">${asset.type}</span>`,
                                `${cleanBrand} / ${cleanModel}`,
                                formatDate(asset.entry_date),
                                htmlspecialchars(asset.office_name),
                                htmlspecialchars(asset.location_name),
                                htmlspecialchars(asset.responsible_name),
                                statusBadge
                            ]);
                        });

                        resultsTable.draw();

                        // Mostrar tarjetas de resultados
                        document.getElementById('resultsCard').classList.remove('d-none');

                        // 2. Rellenar KPIs
                        document.getElementById('stat_total').textContent = stats.total_count;

                        if (stats.total_count > 0) {
                            // Bien más antiguo
                            const oldest = stats.oldest_asset;
                            document.getElementById('stat_oldest_name').textContent = oldest.type;
                            document.getElementById('stat_oldest_name').title = oldest.type;
                            document.getElementById('stat_oldest_date').textContent = `Ingreso: ${formatDate(oldest.entry_date)} (${oldest.custom_code})`;

                            // Bien más nuevo
                            const newest = stats.newest_asset;
                            document.getElementById('stat_newest_name').textContent = newest.type;
                            document.getElementById('stat_newest_name').title = newest.type;
                            document.getElementById('stat_newest_date').textContent = `Ingreso: ${formatDate(newest.entry_date)} (${newest.custom_code})`;

                            // Distribución de estados físicos
                            let conservationHtml = '';
                            for (const status in stats.status_counts) {
                                const count = stats.status_counts[status];
                                const pct = round(count / stats.total_count * 100);
                                const progressClass = getProgressClass(status);
                                
                                conservationHtml += `
                                <div>
                                    <div class="d-flex justify-content-between align-items-center fs-7 lh-1 mb-1">
                                        <span class="text-secondary small fw-medium">${status}</span>
                                        <span class="text-dark fw-bold small">${count} (${pct}%)</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height: 4px;">
                                        <div class="progress-bar ${progressClass} rounded-pill" role="progressbar" style="width: ${pct}%"></div>
                                    </div>
                                </div>`;
                            }
                            document.getElementById('stat_conservation').innerHTML = conservationHtml;

                            // Mostrar panel de KPIs
                            document.getElementById('statsRow').classList.remove('d-none');
                        } else {
                            // Ocultar KPIs si no hay coincidencias
                            document.getElementById('statsRow').classList.add('d-none');
                            Swal.fire({
                                icon: 'info',
                                title: 'Sin Resultados',
                                text: 'No se encontraron bienes patrimoniales con los criterios de búsqueda seleccionados.',
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    let errorMsg = 'No se pudo completar la búsqueda analítica.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        });

        // Helpers de formateo
        function getBadgeClass(status) {
            if (status === 'Bueno') return 'bg-success text-success';
            if (status === 'Regular') return 'bg-info text-info';
            if (status === 'Malo') return 'bg-warning text-warning';
            return 'bg-danger text-danger'; // Chatarra
        }

        function getProgressClass(status) {
            if (status === 'Bueno') return 'bg-success';
            if (status === 'Regular') return 'bg-info';
            if (status === 'Malo') return 'bg-warning';
            return 'bg-danger'; // Chatarra
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr + 'T00:00:00');
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function round(value) {
            return Math.round(value);
        }

        function htmlspecialchars(str) {
            if (typeof str !== 'string') return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
