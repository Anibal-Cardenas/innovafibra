<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shopping-cart"></i> <?= h($title) ?></h2>
        <a href="<?= BASE_URL ?>/ventas/nueva" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Venta
        </a>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" action="<?= BASE_URL ?>/ventas" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small">Desde</label>
                    <input type="date" name="desde" class="form-control" value="<?= isset($filtro_desde) ? h($filtro_desde) : '' ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label small">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="<?= isset($filtro_hasta) ? h($filtro_hasta) : '' ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-secondary">Filtrar</button>
                    <a href="<?= BASE_URL ?>/ventas" class="btn btn-link">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($ventas)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No hay ventas registradas aún.
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Orden Salida</th>
                            <th>Cliente</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">P. Unitario</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Estado</th>
                            <th>Vendedor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                        <tr class="<?= $venta['estado_pago'] === 'cancelado' ? 'table-danger text-muted' : '' ?>">
                            <td><?= $venta['id_venta'] ?></td>
                            <td><?= formatDate($venta['fecha_venta']) ?></td>
                            <td>
                                <span class="badge bg-info"><?= h($venta['codigo_guia_remision']) ?></span>
                            </td>
                            <td><?= h($venta['cliente']) ?></td>
                            <td class="text-end"><?= number_format($venta['cantidad']) ?></td>
                            <td class="text-end"><?= formatCurrency($venta['precio_unitario']) ?></td>
                            <td class="text-end">
                                <strong><?= formatCurrency($venta['precio_total']) ?></strong>
                            </td>
                            <td class="text-center">
                                <?php if ($venta['estado_pago'] === 'cancelado'): ?>
                                    <span class="badge bg-danger">CANCELADO</span>
                                <?php else: ?>
                                    <span class="badge bg-success">ACTIVO</span>
                                <?php endif; ?>
                            </td>
                            <td><?= h($venta['vendedor']) ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/ventas/detalle?id=<?= $venta['id_venta'] ?>" 
                                       class="btn btn-info" 
                                       title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/ventas/guia?id=<?= $venta['id_venta'] ?>" 
                                       class="btn btn-secondary" 
                                       title="Orden de Salida"
                                       target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/ventas/comprobante?id=<?= $venta['id_venta'] ?>" 
                                       class="btn btn-success" 
                                       title="Imprimir Ticket"
                                       target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if ($venta['estado_pago'] !== 'cancelado'): ?>
                                    <form action="<?= BASE_URL ?>/ventas/cancelar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de CANCELAR esta venta? Esta acción restaurará el stock y no se puede deshacer.');">
                                        <input type="hidden" name="id_venta" value="<?= $venta['id_venta'] ?>">
                                        <button type="submit" class="btn btn-danger" title="Cancelar Venta">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </form>
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
    
    <?php endif; ?>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
