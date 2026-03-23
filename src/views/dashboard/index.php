<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-tachometer-alt"></i> <?= h($title) ?>
            </h1>
        </div>
    </div>
    
    <!-- Tarjetas de KPIs -->
    <div class="row g-4 mb-4">
        <!-- Producción Hoy -->
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Producción Hoy</h6>
                            <h2 class="mb-0"><?= number_format($stats['produccion_hoy']) ?></h2>
                            <small>bolsas</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-industry"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Producción Mes -->
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Producción Mes</h6>
                            <h2 class="mb-0"><?= number_format($stats['produccion_mes']) ?></h2>
                            <small>bolsas</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ventas Hoy -->
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Ventas Hoy</h6>
                            <h2 class="mb-0"><?= formatCurrency($stats['ventas_hoy']) ?></h2>
                            <small>&nbsp;</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Eficiencia -->
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Eficiencia</h6>
                            <h2 class="mb-0"><?= $stats['eficiencia_promedio'] ?>%</h2>
                            <small>promedio mes</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inventario Actual -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h4><i class="fas fa-warehouse"></i> Inventario Actual</h4>
        </div>
        
        <!-- Cubos de Fibra -->
        <div class="col-md-4">
            <div class="card border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Fardos de Fibra</h6>
                            <h3 class="mb-0 text-primary">
                                <?php 
                                $stmt = $db->query("SELECT COUNT(*) as total FROM cubos_fibra WHERE estado='disponible'");
                                $cubos = $stmt->fetch()['total'];
                                echo $cubos;
                                ?>
                            </h3>
                            <small class="text-muted">
                                <?php 
                                $stmt = $db->query("SELECT SUM(peso_neto) as total FROM cubos_fibra WHERE estado='disponible'");
                                $pesoTotal = $stmt->fetch()['total'] ?? 0;
                                echo number_format($pesoTotal, 2);
                                ?> kg disponibles
                            </small>
                        </div>
                        <div class="fs-1 text-primary opacity-25">
                            <i class="fas fa-cubes"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?= BASE_URL ?>/compras/nueva-fibra" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Comprar Fibra
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Bolsas Plásticas -->
        <div class="col-md-4">
            <div class="card border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Bolsas Plásticas</h6>
                            <h3 class="mb-0 text-success">
                                <?php 
                                $stmt = $db->query("SELECT cantidad FROM inventario WHERE tipo_item='bolsas_plasticas'");
                                $bolsas = $stmt->fetch()['cantidad'] ?? 0;
                                echo number_format($bolsas, 2);
                                ?>
                            </h3>
                            <small class="text-muted">kg de stock</small>
                        </div>
                        <div class="fs-1 text-success opacity-25">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?= BASE_URL ?>/compras/nueva-bolsas" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Comprar Bolsas
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Producto Terminado -->
        <div class="col-md-4">
            <div class="card border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Producto Terminado</h6>
                            <h3 class="mb-0 text-info">
                                <?php 
                                $totalProducto = 0;
                                if (!empty($inventario_por_calidad)) {
                                    foreach ($inventario_por_calidad as $row) {
                                        $totalProducto += (float)$row['cantidad'];
                                    }
                                }
                                echo number_format($totalProducto, 0);
                                ?>
                            </h3>
                            <small class="text-muted">bolsas de napa</small>
                        </div>
                        <div class="fs-1 text-info opacity-25">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?= BASE_URL ?>/ventas/nueva" class="btn btn-sm btn-info">
                        <i class="fas fa-cash-register"></i> Nueva Venta
                    </a>
                    <?php if (!empty($inventario_por_calidad)): ?>
                    <div class="mt-2">
                        <small class="text-muted">Desglose por calidad:</small>
                        <ul class="list-unstyled mb-0 mt-1">
                            <?php foreach ($inventario_por_calidad as $cal): 
                                $codigoBadge = isset($cal['codigo']) && $cal['codigo'] ? $cal['codigo'] : (isset($cal['id_calidad_napa']) ? $cal['id_calidad_napa'] : '');
                            ?>
                            <li>
                                <span class="badge bg-secondary me-2"><?= h($codigoBadge) ?></span>
                                <?= h($cal['calidad'] ?? $cal['calidad_napa'] ?? '') ?>: <strong><?= number_format($cal['cantidad'] ?? 0, 0) ?></strong>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    
    
    <!-- Resumen y Gráficos -->
    <div class="row mb-4">
        <!-- Resumen Rápido -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Resumen</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Producciones Pendientes de Validación
                            <span class="badge bg-warning rounded-pill"><?= $stats['pendientes_validacion'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Ventas del Mes
                            <span class="badge bg-success rounded-pill"><?= formatCurrency($stats['ventas_mes']) ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Producción del Mes
                            <span class="badge bg-primary rounded-pill"><?= number_format($stats['produccion_mes']) ?> bolsas</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Eficiencia Promedio
                            <span class="badge bg-info rounded-pill"><?= $stats['eficiencia_promedio'] ?>%</span>
                        </div>
                    </div>
                    
                    <?php if (isAdmin()): ?>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/reportes/mermas" class="btn btn-outline-danger btn-sm w-100 mb-2">
                            <i class="fas fa-chart-bar"></i> Ver Reporte de Mermas
                        </a>
                        <a href="<?= BASE_URL ?>/reportes/produccion" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-file-alt"></i> Ver Reporte de Producción
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Gráfico -->
        <?php if (isAdmin()): ?>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-area"></i> Producción vs Ventas (Últimos 7 días)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartProduccionVentas" height="80"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (isAdmin()): ?>
    <script>
    // Datos para el gráfico
    const produccionData = <?= json_encode(array_column($produccionDiaria, 'total')) ?>;
    const ventasData = <?= json_encode(array_column($ventasDiarias, 'total')) ?>;
    const labels = <?= json_encode(array_map(function($item) {
        return date('d/m', strtotime($item['fecha']));
    }, $produccionDiaria)) ?>;
    
    // Crear gráfico
    const ctx = document.getElementById('chartProduccionVentas');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Producción (bolsas)',
                data: produccionData,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Ventas (S/)',
                data: ventasData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Bolsas'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Soles (S/)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
    </script>
    <?php endif; ?>
</div>
