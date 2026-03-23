<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar"></i> <?= h($title) ?></h2>
        <button onclick="window.print()" class="btn btn-secondary no-print">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
    
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reportes/ventas" class="row g-3">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Desde:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= h($fecha_inicio) ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Hasta:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= h($fecha_fin) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Ventas</h6>
                    <h3><?= number_format($resumen['num_ventas']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Ingresos Totales</h6>
                    <h3><?= formatCurrency($resumen['total_ventas']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Detalle de Ventas</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ventas)): ?>
                    <p class="text-muted">No hay ventas en el período seleccionado.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Orden Salida</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?= formatDate($venta['fecha_venta']) ?></td>
                                    <td><small><?= h($venta['codigo_guia_remision']) ?></small></td>
                                    <td><?= h($venta['cliente']) ?></td>
                                    <td class="text-end"><?= number_format($venta['cantidad']) ?></td>
                                    <td class="text-end"><?= formatCurrency($venta['precio_total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ventas por Cliente</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ventas_cliente)): ?>
                    <p class="text-muted">No hay datos</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas_cliente as $vc): ?>
                                <tr>
                                    <td>
                                        <?= h($vc['nombre']) ?><br>
                                        <small class="text-muted"><?= $vc['num_ventas'] ?> ventas</small>
                                    </td>
                                    <td class="text-end">
                                        <strong><?= formatCurrency($vc['total_ventas']) ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
