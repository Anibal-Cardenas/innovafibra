<?php require_once VIEWS_PATH . '/layout/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-boxes"></i> <?= h($title) ?></h2>
        <button onclick="window.print()" class="btn btn-secondary no-print">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
    
    <!-- Estado Actual del Inventario -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Estado Actual del Inventario</h5>
        </div>
        <div class="card-body">
            <?php if (empty($inventario)): ?>
            <p class="text-muted">No hay datos de inventario.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Cantidad</th>
                            <th>Unidad</th>
                            <th class="text-end">Stock Mínimo</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventario as $item): ?>
                        <tr>
                            <td>
                                <strong><?= h(ucfirst(str_replace('_', ' ', $item['tipo_item']))) ?></strong>
                            </td>
                            <td class="text-end">
                                <h5 class="mb-0"><?= formatDecimal($item['cantidad']) ?></h5>
                            </td>
                            <td><?= h($item['unidad_medida']) ?></td>
                            <td class="text-end"><?= formatDecimal($item['stock_minimo']) ?></td>
                            <td class="text-center">
                                <?php
                                $badgeClass = '';
                                switch ($item['estado_alerta']) {
                                    case 'CRÍTICO':
                                        $badgeClass = 'bg-danger';
                                        break;
                                    case 'BAJO':
                                        $badgeClass = 'bg-warning text-dark';
                                        break;
                                    case 'NORMAL':
                                        $badgeClass = 'bg-success';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?> fs-6">
                                    <?= h($item['estado_alerta']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Últimos Movimientos -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Últimos Movimientos de Inventario</h5>
        </div>
        <div class="card-body">
            <?php if (empty($movimientos)): ?>
            <p class="text-muted">No hay movimientos registrados.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Item</th>
                            <th>Tipo Movimiento</th>
                            <th class="text-end">Cantidad</th>
                            <th>Documento</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><small><?= formatDateTime($mov['fecha_movimiento']) ?></small></td>
                            <td><?= h(ucfirst(str_replace('_', ' ', $mov['tipo_item']))) ?></td>
                            <td>
                                <?php
                                $badgeClass = '';
                                $icono = '';
                                switch ($mov['tipo_movimiento']) {
                                    case 'entrada':
                                        $badgeClass = 'bg-success';
                                        $icono = 'fa-arrow-up';
                                        break;
                                    case 'salida':
                                        $badgeClass = 'bg-danger';
                                        $icono = 'fa-arrow-down';
                                        break;
                                    case 'ajuste':
                                        $badgeClass = 'bg-warning text-dark';
                                        $icono = 'fa-sync';
                                        break;
                                    case 'merma':
                                        $badgeClass = 'bg-dark';
                                        $icono = 'fa-exclamation-triangle';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <i class="fas <?= $icono ?>"></i>
                                    <?= h(ucfirst($mov['tipo_movimiento'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?= formatDecimal($mov['cantidad']) ?> <?= h($mov['unidad_medida']) ?>
                            </td>
                            <td>
                                <?php if ($mov['documento_referencia']): ?>
                                <small><?= h($mov['documento_referencia']) ?></small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($mov['observaciones']): ?>
                                <small><?= h($mov['observaciones']) ?></small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
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

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
