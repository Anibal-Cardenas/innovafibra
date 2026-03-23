<?php
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="fas fa-warehouse"></i> <?= h($title) ?></h1>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white"><i class="fas fa-cubes"></i> Stock de Fibra</div>
                <div class="card-body">
                    <?php if (!empty($stock_fibra)): ?>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Calidad</th>
                                <th class="text-end">Fardos</th>
                                <th class="text-end">Peso (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stock_fibra as $f): ?>
                            <tr>
                                <td><?= h($f['calidad_fibra'] ?? $f['nombre'] ?? '') ?></td>
                                <td class="text-end"><?= number_format($f['cantidad_cubos'] ?? $f['cantidad'] ?? 0) ?></td>
                                <td class="text-end"><?= number_format($f['peso_total'] ?? 0, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p class="text-muted">No hay datos de fibra disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white"><i class="fas fa-shopping-bag"></i> Stock de Bolsas Plásticas</div>
                <div class="card-body">
                    <?php if (!empty($stock_bolsas)): ?>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stock_bolsas as $b): ?>
                            <tr>
                                <td>Bolsas Plásticas</td>
                                <td class="text-end">
                                    <h4 class="mb-0"><?= number_format($b['cantidad'], 2) ?> <small class="fs-6"><?= h($b['unidad_medida']) ?></small></h4>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        if ($b['cantidad'] <= 0) echo '<span class="badge bg-danger">Sin Stock</span>';
                                        elseif ($b['cantidad'] < $b['stock_minimo']) echo '<span class="badge bg-warning text-dark">Bajo</span>';
                                        else echo '<span class="badge bg-success">Normal</span>';
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/inventario/editar/<?= $b['id_inventario'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p class="text-muted">No hay stock de bolsas registrado.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white"><i class="fas fa-box-open"></i> Stock de Producto Terminado (Napa)</div>
                <div class="card-body">
                    <?php if (!empty($stock_napa)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Calidad</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_napa as $n): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary me-2"><?= h($n['codigo_calidad']) ?></span>
                                        <strong><?= h($n['nombre_calidad']) ?></strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="fs-5"><?= number_format($n['cantidad']) ?></span> <?= h($n['unidad_medida']) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            if ($n['cantidad'] <= 0) echo '<span class="badge bg-danger">Sin Stock</span>';
                                            elseif ($n['cantidad'] < $n['stock_minimo']) echo '<span class="badge bg-warning text-dark">Bajo</span>';
                                            else echo '<span class="badge bg-success">Normal</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/inventario/editar/<?= $n['id_inventario'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p class="text-muted">No hay stock de producto terminado.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">Últimos Movimientos</div>
                <div class="card-body">
                    <?php if (!empty($movimientos)): ?>
                    <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th class="text-end">Cantidad</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td><?= h($m['fecha_movimiento'] ?? $m['fecha'] ?? '') ?></td>
                                <td><?= h($m['item'] ?? $m['descripcion'] ?? '') ?></td>
                                <td><?= h($m['tipo_movimiento'] ?? $m['tipo'] ?? '') ?></td>
                                <td class="text-end"><?= number_format($m['cantidad'] ?? 0, 2) ?></td>
                                <td><?= h($m['observacion'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php else: ?>
                        <p class="text-muted">No hay movimientos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
