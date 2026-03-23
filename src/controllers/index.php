<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-warehouse"></i> <?= h($title) ?></h2>
        <div>
            <button onclick="window.print()" class="btn btn-secondary no-print">
                <i class="fas fa-print"></i> Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- Sección 1: Materia Prima (Fibra) -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-cubes"></i> Materia Prima (Fibra)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($stock_fibra)): ?>
                <div class="alert alert-warning">No hay stock de fibra disponible (Cubos agotados).</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($stock_fibra as $fibra): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-<?= h($fibra['color_calidad'] ?? 'secondary') ?>">
                            <div class="card-body text-center">
                                <h6 class="text-muted text-uppercase"><?= h($fibra['calidad_fibra'] ?? 'Sin Calidad') ?></h6>
                                <h3 class="mb-0"><?= number_format($fibra['peso_total'], 2) ?> <small class="fs-6">kg</small></h3>
                                <span class="badge bg-<?= h($fibra['color_calidad'] ?? 'secondary') ?> mt-2">
                                    <?= $fibra['cantidad_cubos'] ?> cubos disponibles
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Sección 2: Insumos y Producto Terminado -->
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-boxes"></i> Insumos y Producto Terminado</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ítem / Calidad</th>
                                    <th class="text-end">Stock Actual</th>
                                    <th class="text-center">Unidad</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_general as $item): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['tipo_item'] === 'producto_terminado'): ?>
                                            <i class="fas fa-box text-success me-2"></i>
                                            <strong>Producto Terminado</strong>
                                            <?php if ($item['nombre_calidad']): ?>
                                                <br><small class="text-muted ms-4">Calidad <?= h($item['codigo_calidad']) ?> - <?= h($item['nombre_calidad']) ?></small>
                                            <?php endif; ?>
                                        <?php elseif ($item['tipo_item'] === 'bolsas_plasticas'): ?>
                                            <i class="fas fa-shopping-bag text-info me-2"></i>
                                            <strong>Bolsas Plásticas</strong>
                                        <?php else: ?>
                                            <i class="fas fa-circle text-secondary me-2"></i>
                                            <strong><?= h(ucfirst(str_replace('_', ' ', $item['tipo_item']))) ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="fs-5 fw-bold"><?= number_format($item['cantidad'], 2) ?></span>
                                    </td>
                                    <td class="text-center text-muted"><?= h($item['unidad_medida']) ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $stock = $item['cantidad'];
                                            $min = $item['stock_minimo'];
                                            if ($stock <= 0) {
                                                echo '<span class="badge bg-danger">Sin Stock</span>';
                                            } elseif ($stock < $min) {
                                                echo '<span class="badge bg-warning text-dark">Bajo</span>';
                                            } else {
                                                echo '<span class="badge bg-success">Normal</span>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 3: Últimos Movimientos (Kardex) -->
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Movimientos Recientes</h5>
                    <a href="<?= BASE_URL ?>/reportes/inventario" class="btn btn-sm btn-light text-dark">Ver Todo</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($movimientos)): ?>
                            <div class="p-3 text-center text-muted">No hay movimientos recientes.</div>
                        <?php else: ?>
                            <?php foreach ($movimientos as $mov): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <?php
                                        $icon = 'circle';
                                        $color = 'secondary';
                                        if ($mov['tipo_movimiento'] == 'entrada') { $icon = 'arrow-down'; $color = 'success'; }
                                        elseif ($mov['tipo_movimiento'] == 'salida') { $icon = 'arrow-up'; $color = 'danger'; }
                                        elseif ($mov['tipo_movimiento'] == 'ajuste') { $icon = 'sync'; $color = 'warning'; }
                                        ?>
                                        <i class="fas fa-<?= $icon ?> text-<?= $color ?> me-1"></i>
                                        <?= ucfirst($mov['tipo_movimiento']) ?>
                                    </h6>
                                    <small class="text-muted"><?= date('d/m H:i', strtotime($mov['fecha_movimiento'])) ?></small>
                                </div>
                                <p class="mb-1 small">
                                    <?= h(ucfirst(str_replace('_', ' ', $mov['tipo_item']))) ?>
                                    <strong class="ms-1"><?= number_format($mov['cantidad'], 2) ?> <?= h($mov['unidad_medida']) ?></strong>
                                </p>
                                <small class="text-muted">Ref: <?= h($mov['documento_referencia'] ?: '-') ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>